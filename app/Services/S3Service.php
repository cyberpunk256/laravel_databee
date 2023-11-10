<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Aws\S3\S3Client;

class S3Service
{
    protected S3Client $s3client;
    public function __construct()
    {
        $this->s3client = new S3Client([
            'region' => config('filesystems.disks.s3.region'), // S3のリージョン
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret')
            ],
        ]);
    }
    public function getPresignedUrl(String $file_path)
    {
        $cmd = $this->s3client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $file_path,
            'ContentType' => 'application/octet-stream', // ファイルのMIMEタイプ
        ]);

        $presignedUrl = $this->s3client
            ->createPresignedRequest($cmd, '+30 minutes')
            ->getUri();

        return $presignedUrl;
    }

    public function upload($file, $file_path) 
    {
        $this->s3client->putObject([
            'Bucket'     => config('filesystems.disks.s3.bucket'),
            'Key'        => $file_path,
            'Body'       => file_get_contents($file), // 画像ファイルの内容を取得
        ]);

        return true;
    }

    public function getList()
    {
        $list = $this->s3client->listObjects([
            'Bucket'     => config('filesystems.disks.s3.bucket'),
        ]);
        return $list['Contents'];
    }

    public function getVideo(String $path)
    {
        try {

            $result = $this->s3client->getObject([
                'Key' => $path,
            ]);
            $result = $this->s3client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $path,
            ]);

            $stream = new Stream($result['Body']);

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type' => $result['ContentType'],
                'Content-Disposition' => 'inline; filename="' . $videoKey . '"',
                'Accept-Ranges' => 'bytes',
                'Content-Length' => $result['ContentLength']
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFile(String $path) 
    {
        
    }
}
