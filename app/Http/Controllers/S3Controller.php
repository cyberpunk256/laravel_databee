<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Aws\S3\S3Client;

use App\Services\S3Service;

class S3Controller extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function getVideo(Request $request)
    {
        return response()->json(["xxxxx" => "xxasdfasdf"]);
        try {
            $path = $request->input('path');
            return $this->s3service->getVideo($path);
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
