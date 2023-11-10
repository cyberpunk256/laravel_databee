<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\S3Service;

class S3Controller extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function getPresignedUrl(Request $request) 
    {
        try {
            $path = $request->input('path');
            $presignedUrl = $this->s3service->getPresignedUrl($path);
            return response()->json([
                "success" => __("success_complete"),
                'presigned_url' => $presignedUrl,
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 500);
        }
    }

    public function getFile(Request $request)
    {
        try {
            $path = $request->input('path');
            return $this->s3service->getFile($path);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 500);
        }
    }
}
