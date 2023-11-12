<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateCaptureRequest;

class CaptureController extends Controller
{
    public function fileUpload(UpdateCaptureRequest $request) 
    {
        try {
            $imageData = $request->file('file_str');
            $attrs = [
                $request->input('playtime');
            ]
            $playtime = $request->input('playtime');
            // Base64データをデコードしてファイルに保存
            $imageBody = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $file_path = date("Ymd") . "/" . Str::uuid() . "." . '.png'; // 一意のファイル名を生成
            $status = $this->s3service->upload($imageBody, $file_path);

            Capture::create([
                "url" => $file_path,
                "position: "
            ])
            
            return response()->json([
                "success" => __("success_complete"),
                'file_path' => $file_path,
                'file_name' => $file_name
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }
}
