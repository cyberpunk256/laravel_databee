<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        $limit = $request->get('limit', 10);
        if($limit < 0) $limit = 0;
        $data = $query->paginate($limit);

        return response()->json($data);
    }

    public function deleteRecords(Request $request) {
        try {
            \DB::beginTransaction();
            $ids = $request->input('ids');
            $records_query = Capture::whereIn('id', $ids);

            $records =  (clone $records_query)->get();

            $delete_files = [];
            foreach ($records as $record) {
                $delete_files[] = [
                    'Key' => $record->url
                ];
            }

            (clone $records_query)->delete();
            $result = $this->s3service->deleteFiles($delete_files);

            \DB::commit();
            return response()->json(['success' => __("success_delete")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }

    public function store(Request $request) 
    {
        try {
            $file = $request->file('file');
            $attrs = [
                "user_id" => Auth()->user()->id,
                "media_id" => $request->input('media_id'),
                "playtime" => $request->input('playtime'),
                "rotation" => $request->input('rotation'),
                "zoom" => $request->input('zoom'),
                "lat" => $request->input('lat'),
                "long" => $request->input('long'),
                "url" => date("Ymd") . "/" . Str::uuid() . "." . $file->getClientOriginalExtension()
            ];
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
