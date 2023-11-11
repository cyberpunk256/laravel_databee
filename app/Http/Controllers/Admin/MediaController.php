<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Http\Requests\Admin\MediaUpdateRequest;

use App\Services\S3Service;
use Ramsey\Uuid\Type\Integer;

class MediaController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $query = Media::query()
            ->withTrashed()
            ->select('*')
            ->with([
                'admin' => function($sub_query) {
                    $sub_query->select('id', 'name');
                }
            ])
            ->addSelect(\DB::raw("
                CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END as status
            "))
            ->when($request->get('search'), function ($query, $search) {
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

    public function updateStatus(Request $request, $id)
    {
        try {
            $media = Media::withTrashed()->find($id);
            if($media->trashed()) {
                $media->restore();
            } else {
                $media->delete();
            }

            return back()->with(['success' => __("success_update")]);

        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with(['error' => __("success_error")], 404);
        }
    }

    public function store(MediaUpdateRequest $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $input = $request->only([
                'name',
                'video',
                'image',
                'gpx',
                'type',
                'image_lat',
                'image_long',
            ]);

            $data = [
                'admin_id' => $admin->id,
                'name' => $input['name'],
                'image_lat' => $input['image_lat'],
                'image_long' => $input['image_long'],
            ];

            if($input['type'] == 1) {
                $this->s3service->fileMovieFromTmp($input['video']['file_name']);
                $this->s3service->fileMovieFromTmp($input['gpx']['file_name']);
                $data['media_path'] = $input['video']['file_name'];
                $data['video_duration'] = $input['video']['video_duration'];
                $data['gpx_path'] = $input['gpx']['file_name'];
            } else {
                $this->s3service->fileMovieFromTmp($input['image']['file_name']);
                $data['media_path'] = $input['image']['file_name'];
            }

            Media::create($data);

            return back()->with(['success' => __("success_save")]);

        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with(['error' => __("success_error")], 404);
        }
    }

    public function edit($id)
    {
        $media = Media::withTrashed()->find($id);
        return Inertia::render('Admin/Media/Edit', [
            'record' => $media
        ]);
    }

    public function createPresignedUrl(Request $request) 
    {
        try {
            $file_extension = $request->input('extension');
            $file_name = Str::uuid() . "." . $file_extension;
            $file_path = "tmp/" . $file_name;
            
            $presignedUrl = $this->s3service->createPresignedUrl($file_path);
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
            ], 404);
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
            ], 404);
        }
    }
}
