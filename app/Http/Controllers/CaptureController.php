<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\CaptureUpdateRequest;
use App\Services\S3Service;

use App\Models\Capture;

class CaptureController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $user = Auth()->user();
        $query = Capture::query()
            ->where('user_id', $user->id)
            ->when($request->get('search'), function ($query, $search) {
                return $query->where('url', 'LIKE', "%$search%");
            })->when($request->get('sort'), function ($query, $sortBy) {
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $query->paginate($request->get('limit', 10));

        return response()->json($data);
    }

    public function deleteRecords(Request $request) {
        try {
            $ids = $request->input('ids');
            $records_query = Capture::whereIn('id', $ids);

            $records =  (clone $records_query)->get();
            (clone $records_query)->forceDelete();

            $delete_files = [];
            foreach ($records as $record) {
                if($record->type == 1) { // video
                    $delete_files[] = [
                        'Key' => $record->url
                    ];
                }
            }
            $result = $this->s3service->deleteFiles($delete_files);

            return response()->json(['success' => __("success_delete")]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }

    public function fileUpload(Request $request) 
    {
        try {
            $file_str = $request->input('file');
            dd($file_str);
            $attrs = [
                "user_id" => Auth()->user()->id,
                "media_id" => $request->input('media_id'),
                "playtime" => $request->input('playtime'),
                "rotation" => $request->input('rotation'),
                "zoom" => $request->input('zoom'),
                "lat" => $request->input('lat'),
                "long" => $request->input('long'),
            ];
            $attrs['url'] = date("Ymd") . "/" . Str::uuid() . '.png'; // 一意のファイル名を生成
            $this->s3service->upload($file, $attrs['url']);

            Capture::create($attrs);
            
            return response()->json([
                "success" => __("success_capture"),
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }
}
