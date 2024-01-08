<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;

use App\Models\Media;
use App\Models\Setting;

class MediaConvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Media $media;
    protected $outputPrefix;
    /**
     * Create a new job instance.
     */
    public function __construct(Media $media, $outputPrefix)
    {
        $this->media = $media;
        $this->outputPrefix = $outputPrefix;
    }

    /**
     * Execute the job.
     */
    public function handle(MediaConvertClient $mediaConvertClient): void
    {
        try {
            $jobSetting = $this->getMediaConvertSetting($this->media->media_path, $this->outputPrefix);
            $arnId = config('app.arn_id');
            $region = config('app.region');
            
            $result = $mediaConvertClient->createJob([
                "Role" => "arn:aws:iam::{$arnId}:role/service-role/MediaConvert_Default_Role",
                "Settings" => $jobSetting,
                "Queue" => "arn:aws:mediaconvert:{$region}:{$arnId}:queues/Default",
                "UserMetadata" => [],
            ]);

            $jobId = $result['Job']['Id'];

            // ジョブが完了するまでポーリング
            do {
                $job = $mediaConvertClient->getJob(['Id' => $jobId]);
                $jobStatus = $job['Job']['Status'];

                if ($jobStatus === 'COMPLETE') {
                    // ジョブが完了した場合の処理

                    \Log::info('MediaConvertJob Status - COMPLETE', $job['Job']['OutputGroupDetails'][0]);

                    $outputDetails = $job['Job']['OutputGroupDetails'][0]['OutputDetails'][0];
                    $newFilePath = $outputDetails['OutputFilePaths'][0];

                    $this->media->update([
                        'queue' => 1, // complete
                        'media_path' => $newFilePath
                    ]);

                    \Log::info('MediaConvertJob Status - COMPLETE', $job['Job']);
                } elseif ($jobStatus === 'ERROR') {
                    \Log::error('MediaConvertJob Status - ERROR');
                }

                sleep(5); // 5秒ごとにポーリング

            } while (true);
        } catch (AwsException $e) {
            \Log::error($e);
        }
    }

    protected function getMediaConvertSetting(String $inputPath, String $outputPrefix) {
        $bucket_name = config('filesystems.disks.s3.bucket');
        $absInputPath = "s3://" . $bucket_name . "/" . $inputPath;
        $absOutputPrefix = "s3://" . $bucket_name . "/" . $outputPrefix;
        return [
            "Inputs" =>[
              [
                "AudioSelectors" =>[
                  "Audio Selector 1" =>[
                        "DefaultSelection" =>"DEFAULT"
                    ]
                ],
                "VideoSelector" =>[],
                "TimecodeSource" =>"ZEROBASED",
                    "FileInput" => $absInputPath
                ]
            ],
            "OutputGroups" => [
                [
                  "Name" => "Media Files",
                  "Outputs" => [
                    [
                      "ContainerSettings" => [
                        "Container" => "M3U8",
                        "M3u8Settings" => []
                      ],
                      "VideoDescription" => [
                        "Width" => 1280,
                        "Height" => 720,
                        "CodecSettings" => [
                          "Codec" => "H_264",
                          "H264Settings" => [
                            "ParNumerator" => 16,
                            "FramerateDenominator" => 1,
                            "MaxBitrate" => 50000,
                            "ParDenominator" => 9,
                            "FramerateControl" => "SPECIFIED",
                            "RateControlMode" => "QVBR",
                            "FramerateNumerator" => 30,
                            "SceneChangeDetect" => "TRANSITION_DETECTION"
                          ]
                        ]
                      ],
                      "AudioDescriptions" => [
                        [
                          "AudioSourceName" => "Audio Selector 1",
                          "CodecSettings" => [
                            "Codec" => "AAC",
                            "AacSettings" => [
                              "Bitrate" => 96000,
                              "CodingMode" => "CODING_MODE_2_0",
                              "SampleRate" => 48000
                            ]
                          ]
                        ]
                      ],
                      "OutputSettings" => [
                        "HlsSettings" => []
                      ],
                      "NameModifier" => "_hls"
                    ]
                  ],
                  "OutputGroupSettings" => [
                    "Type" => "HLS_GROUP_SETTINGS",
                    "HlsGroupSettings" => [
                      "SegmentLength" => 10,
                      "Destination" => $absOutputPrefix,
                      "MinSegmentLength" => 0
                    ]
                  ]
                ]
            ],
            "StatusUpdateInterval" => "SECONDS_60",
            "Priority" => 0
        ];
    }
}

