<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Aws\S3\S3Client;
use Aws\CloudFront\CloudFrontClient;
use Illuminate\Filesystem\FilesystemAdapter;

class AWSService
{
    protected S3Client $s3client;
    protected CloudFrontClient $cf_client;
    public function __construct()
    {
        if(config('app.env') == 'production') {
            $this->s3client = new S3Client([
                'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                'version' => 'latest'
            ]);
            $this->cf_client = new CloudFrontClient([
                'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                'version' => 'latest'
            ]);
        } else {
            $this->s3client = new S3Client([
                'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                'version' => 'latest',
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret')
                ],
            ]);
            $this->cf_client = new CloudFrontClient([
                'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                'version' => 'latest',
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret')
                ],
            ]);
        }
    }
    
    public function createPresignedUrl(String $path)
    {
        $cmd = $this->s3client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
            // 'ACL' => 'public-read'
        ]);

        $presignedUrl = $this->s3client
            ->createPresignedRequest($cmd, '+30 minutes')
            ->getUri();

        return $presignedUrl;
    }

    public function getPresignedUrl(String $path)
    {
        $resourceUrl = 'https://' . config('filesystems.disks.s3.cloudfront_domain') . '/' . $path;
        // $signedUrl = $this->cf_client->getSignedUrl([
        //     'url' => $resourceUrl,
        //     'expires' => time() + 60 * 30, // URL valid for 60 seconds (adjust as needed)
        //     'key_pair_id' => config('filesystems.disks.s3.cloudfront_key_pair_id'),
        //     'private_key' => storage_path(config('filesystems.disks.s3.cloudfront_private_key_path'))
        // ]);

        return $resourceUrl;
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

    public function move($from_path, $to_path) 
    {
        // ファイルをコピー
        $this->s3client->copyObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $to_path,
            'CopySource' => config('filesystems.disks.s3.bucket') . "/" . $from_path,
        ]);
    
        // 元のファイルを削除
        return $this->s3client->deleteObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $from_path,
        ]);
    }

    public function deleteFiles($files) {
        // 元のファイルを削除
        foreach ($files as $file) {
            if(str_ends_with($file['Key'], 'm3u8')) {
                //  remove suffix "index.m3u8" from string
                $folder_path = substr($file['Key'], 0, -11);
                $this->s3client->deleteMatchingObjects(config('filesystems.disks.s3.bucket'), $folder_path);
                $this->s3client->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $folder_path . "/",
                ]);
            } else {
                $this->s3client->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $file['Key'],
                ]);
            }
        }
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
