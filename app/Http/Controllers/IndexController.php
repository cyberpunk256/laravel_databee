<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\S3Service;

class IndexController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        // $list = $this->s3service->getList();
        // dd(array_column($list, 'Key'));
        return Inertia::render('Index');
    }
}
