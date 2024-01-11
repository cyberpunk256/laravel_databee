<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AWSService;

class MediaController extends Controller
{
    protected AWSService $aws_service;
    public function __construct()
    {
        $this->aws_service = new AWSService();
    }

    public function getPresignedUrl(Request $request) 
    {
        $file_path = $request->input('path');
        
        $presignedUrl = $this->aws_service->getPresignedUrl($file_path);
        return response()->json([
            'presigned_url' => $presignedUrl
        ]);
    }
    
    public function getFile(Request $request) 
    {
        $path = $request->input('path');
        return $this->aws_service->getFile($path);
    }
}
