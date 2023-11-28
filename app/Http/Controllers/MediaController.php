<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\S3Service;

class MediaController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function getPresignedUrl(Request $request) 
    {
        $file_path = $request->input('path');
        
        $presignedUrl = $this->s3service->getPresignedUrl($file_path);
        return response()->json([
            'presigned_url' => $presignedUrl
        ]);
    }
    
    public function getFile(Request $request) 
    {
        $path = $request->input('path');
        return $this->s3service->getFile($path);
    }
}
