<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class S3Controller extends Controller
{
    public function getPresignedUrl(Request $request)
    {
        try {
            $fileName = uniqid();
            $folder = $request->input('folder'); // ファイル名を取得する
    
            $s3 = Storage::disk('s3');
            $client = $s3->getDriver()->getAdapter()->getClient();
    
            $cmd = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $folder . "/" . $fileName
            ]);
    
            $request = $client->createPresignedRequest($cmd, '+20 minutes');
    
            return back()->with([
                "success" => true,
                "data" => [
                    'presigned_url' => (string) $request->getUri()
                ]
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with([
                "error" => __('success_error')
            ]);
        }
    }
}
