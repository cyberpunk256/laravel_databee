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
      $arnId = config('filesystems.disks.s3.arn_id');
      $region = config('filesystems.disks.s3.region');
      
      $result = $mediaConvertClient->createJob([
          "Role" => "arn:aws:iam::{$arnId}:role/service-role/MediaConvert_Default_Role",
          "Settings" => $jobSetting,
          "Queue" => "arn:aws:mediaconvert:{$region}:{$arnId}:queues/Default",
          "UserMetadata" => [],
      ]);

      $jobId = $result['Job']['Id'];
      $this->media->update([
          'status' => 0, // converting
          'job_id' => $jobId
      ]);
    } catch (\Throwable $exception) {
        \Log::error($exception);
    }
  }

  protected function getMediaConvertSetting(String $inputPath, String $outputPrefix) {
      $bucket_name = config('filesystems.disks.s3.bucket');
      $absInputPath = "s3://" . $bucket_name . "/" . $inputPath;
      $absOutputPrefix = "s3://" . $bucket_name . "/" . $outputPrefix;
      $mc_setting = [
        "TimecodeConfig" =>  [
          "Source" =>  "ZEROBASED"
        ],
        "OutputGroups" =>  [
          [
            "Name" =>  "Apple HLS",
            "Outputs" =>  [
            ],
            "OutputGroupSettings" =>  [
              "Type" =>  "HLS_GROUP_SETTINGS",
              "HlsGroupSettings" =>  [
                "SegmentLength" =>  10,
                "Destination" =>  $absOutputPrefix . "/index",
                "MinSegmentLength" =>  0
              ]
            ]
          ]
        ],
        "FollowSource" =>  1,
        "Inputs" =>  [
          [
            "AudioSelectors" =>  [
              "Audio Selector 1" =>  [
                "DefaultSelection" =>  "DEFAULT"
              ]
            ],
            "VideoSelector" =>  [],
            "TimecodeSource" =>  "ZEROBASED",
            "FileInput" => $absInputPath
          ]
        ]
      ];
    $db_setting_str = Setting::where('key', 'media_conver_options')->first();
    $db_setting = json_decode($db_setting_str->value);
    foreach($db_setting as $detail) {
      $resolution = $detail->resolution;
      $bitrate = $detail->bitrate;
      $mc_setting['OutputGroups'][0]['Outputs'][] = [
        "ContainerSettings" =>  [
          "Container" =>  "M3U8",
          "M3u8Settings" =>  []
        ],
        "VideoDescription" =>  [
          "Height" => intval($resolution),
          "CodecSettings" =>  [
            "Codec" =>  "H_264",
            "H264Settings" =>  [
              "MaxBitrate" =>  intval($bitrate),
              "RateControlMode" =>  "QVBR",
              "SceneChangeDetect" =>  "TRANSITION_DETECTION"
            ]
          ]
        ],
        "AudioDescriptions" =>  [
          [
            "CodecSettings" =>  [
              "Codec" =>  "AAC",
              "AacSettings" =>  [
                "Bitrate" =>  96000,
                "CodingMode" =>  "CODING_MODE_2_0",
                "SampleRate" =>  48000
              ]
            ]
          ]
        ],
        "OutputSettings" =>  [
          "HlsSettings" =>  []
        ],
        "NameModifier" =>  "_{$resolution}"
      ];
    }
    return $mc_setting;
  }
}

