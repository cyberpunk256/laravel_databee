<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Media;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Media::insert([
            [
                'admin_id' => 2,
                'name' => '動画1',
                'type' => 1,
                'video_duration' => 38,
                'media_path' => '20231112/VID_20230501_181734_00_009.mp4',
                'gpx_path' => '20231112/VID_20230501_181734_00_009.gpx',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'admin_id' => 3,
                'name' => '動画2',
                'type' => 1,
                'video_duration' => 46,
                'media_path' => '20231112/VID_20230501_181829_00_010.mp4',
                'gpx_path' => '20231112/VID_20230501_181829_00_010.gpx',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'admin_id' => 3,
                'name' => '動画3',
                'type' => 1,
                'video_duration' => 46,
                'media_path' => '20231112/VID_20230501_181941_00_011.mp4',
                'gpx_path' => '20231112/VID_20230501_181941_00_011.gpx',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
