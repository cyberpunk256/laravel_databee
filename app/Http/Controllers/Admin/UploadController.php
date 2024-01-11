<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Aws\MediaConvert\MediaConvertClient;

use App\Models\Setting;
use App\Models\Media;

use App\Jobs\MediaConvertJob;

class UploadController extends Controller
{
    protected MediaConvertClient $mediaConvertClient;
    public function __construct(MediaConvertClient $mediaConvertClient)
    {
        $this->mediaConvertClient = $mediaConvertClient;
    }
    public function index()
    {
        $record = Media::find(27);
        $outputPrefix = "main/20240109/122922000000527";
        MediaConvertJob::dispatch($record, $outputPrefix)->onQueue('default');
        // $queue = $this->mediaConvertClient->getQueue([
        //     'Name' => 'Default',
        // ]);
        // dd($queue);
        // $endPoint = $this->mediaConvertClient->describeEndpoints();
        return Inertia::render('Admin/Upload/Index');
    }
    public function store()
    {

    }
}
