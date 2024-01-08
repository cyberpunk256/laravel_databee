<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Aws\MediaConvert\MediaConvertClient;

class UploadController extends Controller
{
    protected MediaConvertClient $mediaConvertClient;
    public function __construct(MediaConvertClient $mediaConvertClient)
    {
        $this->mediaConvertClient = $mediaConvertClient;
    }
    public function index()
    {
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
