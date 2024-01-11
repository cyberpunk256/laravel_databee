<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Aws\MediaConvert\MediaConvertClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MediaConvertClient::class, function ($app) {
            if(config('app.env') == 'production') {
                return new MediaConvertClient([
                    'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                    'version' => 'latest'
                ]);
            } else {
                return new MediaConvertClient([
                    'region' => config('filesystems.disks.s3.region'), // S3のリージョン
                    'version' => 'latest',
                    'credentials' => [
                        'key' => config('filesystems.disks.s3.key'),
                        'secret' => config('filesystems.disks.s3.secret')
                    ],
                ]);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
