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

    public function fileUpload(CaptureUpdateRequest $request) 
    {
        try {
            $imageData = $request->file('file_str');
            $attrs = [
                "user_id" => Auth()->user()->id,
                "media_id" => $request->input('media_id'),
                "playtime" => $request->input('playtime'),
                "rotation" => $request->input('rotation'),
                "zoom" => $request->input('zoom'),
            ];
            $imageBody = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $attrs['url'] = date("Ymd") . "/" . Str::uuid() . "." . '.png'; // 一意のファイル名を生成
            $this->s3service->upload($imageBody, $attrs['url']);

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
