<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Http\Requests\Admin\MediaUpdateRequest;

use App\Services\S3Service;

class MediaController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $query = Media::query()->when($request->get('search'), function ($query, $search) {
            return $query->where('name', 'LIKE', "%$search%");
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $data = $query->paginate($request->get('limit', 10));

        return Inertia::render('Admin/Media/Index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Media/Create');
    }

    public function store(MediaUpdateRequest $request)
    {
        $data = $request->only([
            'name', 
            'type', 
            'url', 
            'gpx_url', 
            'length', 
            'image_lat', 
            'image_long'
        ]);

        $media = Media::create($data)->delete();

        return back()->with([
            'success' => true,
            "data" => $media
        ]);
    }

    public function createPresignedUrl(Request $request) 
    {
        try {
            $file_extension = $request->input('extension');
            $file_content_type = $request->input('type');
            $file_name = Str::uuid() . "." . $file_extension;
            $file_path = "tmp/" . $file_name;
            
            $presignedUrl = $this->s3service->createPresignedUrl($file_path, $file_content_type);
            return response()->json([
                "success" => __("success_complete"),
                'presigned_url' => $presignedUrl,
                'file_path' => $file_path,
                'file_name' => $file_name
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 500);
        }
    }

    public function fileUpload(Request $request) 
    {
        try {
            $file = $request->file('file');
            $file_name = Str::uuid() . "." . $file->getClientOriginalExtension();
            $file_path = "tmp/" . $file_name;
            $status = $this->s3service->upload($file, $file_path);
            
            return response()->json([
                "success" => __("success_complete"),
                'file_path' => $file_path,
                'file_name' => $file_name
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 500);
        }
    }
}
