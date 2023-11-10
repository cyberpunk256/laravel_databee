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
    
    public function createPresignedUrl(String $path, String $content_type)
    {
        $cmd = $this->s3client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
        ]);

        $presignedUrl = $this->s3client
            ->createPresignedRequest($cmd, '+30 minutes')
            ->getUri();

        return $presignedUrl;
    }

    public function getPresignedUrl(String $path, String $content_type)
    {
        $cmd = $this->s3client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
        ]);

        $presignedUrl = $this->s3client
            ->createPresignedRequest($cmd, '+30 minutes')
            ->getUri();

        return $presignedUrl;
    }

    public function upload($file, $path) 
    {
        $this->s3client->putObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $path,
            'Body'   => file_get_contents($file), // 画像ファイルの内容を取得
        ]);

        return true;
    }

    public function getList()
    {
        $list = $this->s3client->listObjects([
            'Bucket' => config('filesystems.disks.s3.bucket'),
        ]);
        return $list['Contents'];
    }

    public function getFile(String $path) 
    {
        $result = $this->s3client->getObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
        ]);

        // Get the contents of the file
        $fileContent = $result['Body']->getContents();

        // You can then do something with $fileContent, like return it as a response
        return response($fileContent, 200)->header('Content-Type', $result['ContentType']);
    }
}
