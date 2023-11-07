<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class S3Controller extends Controller
{
    public function getPresignedUrl(Request $request)
    {
        try {
            $fileName = uniqid();
            $folder = $request->input('folder'); // ファイル名を取得する
    
            $s3 = Storage::disk('s3');
            $client = $s3->getClient();

            $cmd = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $folder . "/" . $fileName
            ]);
    
            $request = $client->createPresignedRequest($cmd, '+20 minutes');
    
            return response()->json([
                'presigned_url' => (string) $request->getUri()
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ]);
        }
    }

    public function upload(Request $request) 
    {
         // Set file attributes.
         $filepath = 'tmp';
         $file = $request->file('file');
         $filename = "11111112.mp4"; // Hidden input with a generated value

         // Upload to S3, overwriting if filename exists.
         $result = File::streamUpload($filepath, $filename, $file, true);
         return $result;
    }
}
