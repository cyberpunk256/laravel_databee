<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AWSService;

class DeleteMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $aws_service = new AWSService();
            $tmp_obj = $aws_service->getList("tmp")->toArray();
            $tmp_list = $tmp_obj['Contents'];


            // 条件に基づいてオブジェクトを削除
            foreach ($tmp_list as $object) {
                var_dump($object);
                // \Log::info('object: ' . var_dump($object));
                $lastModified = strtotime($object['LastModified']);
                
                if ($lastModified < strtotime('-1 day') && $object['Key'] != "tmp/") {
                    $aws_service->deleteFiles([$object]);
                    \Log::info('Deleted: ' . $object['Key']);
                }
            }

            $conver_obj = $aws_service->getList("convert")->toArray();
            $convert_list = $conver_obj['Contents'];
            // 条件に基づいてオブジェクトを削除
            foreach ($convert_list as $object) {
                // \Log::info('object: ' . var_dump($object));
                $lastModified = strtotime($object['LastModified']);
                
                if ($lastModified < strtotime('-1 day') && $object['Key'] != "convert/") {
                    // $aws_service->deleteFiles([$object]);
                    \Log::info('Deleted: ' . $object['Key']);
                }
            }
        } catch (\Throwable $exception) {
            \Log::error($exception);
        }
    }
}
