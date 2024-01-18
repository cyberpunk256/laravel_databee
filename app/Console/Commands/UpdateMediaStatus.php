<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\MediaConvert\MediaConvertClient;

use App\Models\Media;

class UpdateMediaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-media-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(MediaConvertClient $mediaConvertClient)
    {
        try {
            $bucket_name = config('filesystems.disks.s3.bucket');
            $medias = Media::query()
                ->where('status', 0)
                ->whereNotNull('job_id')
                ->get();
            foreach($medias as $media) {
                $job = $mediaConvertClient->getJob(['Id' => $media->job_id]);
                $jobStatus = $job['Job']['Status'];
                if ($jobStatus === 'COMPLETE') {
                    $option = $job['Job']['OutputGroupDetails'][0]["OutputDetails"][0];
                    $destination = $job['Job']['Settings']['OutputGroups'][0]['OutputGroupSettings']['HlsGroupSettings']['Destination'];
                    $full_path = $destination . ".m3u8";
                    $abs_path = str_replace("s3://" . $bucket_name . "/", "", $full_path);  

                    $media->update([
                        'status' => 1, // complete
                        'media_path' => $abs_path,
                        'job_id' => null,       
                        'video_duration' => $option['DurationInMs']
                    ]);
                    \Log::info('MediaConvertJob Status - COMPLETE - Media ID:' . $media->id);
                } elseif ($jobStatus === 'ERROR') {
                    $media->update([
                        'status' => 2, // error
                    ]);
                    \Log::info('MediaConvertJob Status - ERROR - Media ID:' . $media->id);
                }
            }
        } catch (\Throwable $exception) {
            \Log::error($exception);
        }
    }
}
