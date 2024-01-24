<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::insert([
            [
                'key' => 'map_gpx_weight',
                'value' => 15,
            ],
            [
                'key' => 'map_default_zoom',
                'value' => 18,
            ],
            [
                'key' => 'map_max_zoom',
                'value' => 20,
            ],
            [
                'key' => 's3_upload_folder',
                'value' => "bulk_upload",
            ],
            [
                'key' => 'media_conver_options',
                'value' => json_encode([
                    [
                        'resolution' => 1080,
                        'bitrate' => 8000000,
                    ],
                    [
                        'resolution' => 720,
                        'bitrate' => 5000000,
                    ],
                    [
                        'resolution' => 480,
                        'bitrate' => 2500000,
                    ]
                ]),
            ],
        ]);
    }
}
