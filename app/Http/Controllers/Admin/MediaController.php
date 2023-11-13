<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUpdateRequest;
use App\Services\S3Service;

use App\Models\Media;

class MediaController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

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

        if($admin->role == 2) {
            $query->whereHas('admin', function ($query) use($admin) {
                $query->where('group_id', $admin->group_id); 
            });
        }
        if($admin->role == 3) {
            $query->where('admin_id', $admin->id);
        }

        $limit = $request->get('limit', 10);
        if($limit < 0) $limit = 0;
        $data = $query->paginate($limit);

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
                'type' => $input['type'],
                'image_lat' => $input['image_lat'],
                'image_long' => $input['image_long'],
            ];

            if($input['type'] == 1) {
                $data['media_path'] = date("Ymd") . "/" . $input['video']['file_name'];
                $data['video_duration'] = $input['video']['video_duration'];
                $data['gpx_path'] = date("Ymd") . "/" . $input['gpx']['file_name'];
                $this->s3service->move($input['video']['file_path'], $data['media_path']);
                $this->s3service->move($input['gpx']['file_path'], $data['gpx_path']);
            } else {
                $data['media_path'] = date("Ymd") . "/" . $input['image']['file_name'];
                $this->s3service->move($input['image']['file_path'], $data['media_path']);
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

    public function update(MediaUpdateRequest $request, $medium)
    {
        try {
            \DB::beginTransaction();
            $record = Media::withTrashed()->find($medium);
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
                'type' => $input['type'],
                'image_lat' => $input['image_lat'],
                'image_long' => $input['image_long'],
            ];

            $delete_files = [];
            if($input['type'] == 1) {
                if(isset($input['video'])) {
                    $data['media_path'] = date("Ymd") . "/" . $input['video']['file_name'];
                    $data['video_duration'] = $input['video']['video_duration'];
                    $this->s3service->move($input['video']['file_path'], $data['media_path']);
                    if($record->media_path) {
                        $delete_files[] = [ 'Key' => $record->media_path ];
                    }
                }
                if(isset($input['gpx'])) {
                    $data['gpx_path'] = date("Ymd") . "/" . $input['gpx']['file_name'];
                    $this->s3service->move($input['gpx']['file_path'], $data['gpx_path']);
                    if($record->gpx_path) {
                        $delete_files[] = [ 'Key' => $record->gpx_path ];
                    }
                }
            } else {
                if(isset($input['image'])) {
                    $data['media_path'] = date("Ymd") . "/" . $input['image']['file_name'];
                    $this->s3service->move($input['image']['file_path'], $data['media_path']);
                    if($record->media_path) {
                        $delete_files[] = [ 'Key' => $record->media_path ];
                    }
                }
            }

            $record->update($data);
            $this->s3service->deleteFiles($delete_files);

            \DB::commit();
            return back()->with(['success' => __("success_save")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return back()->with(['error' => __("success_error")], 404);
        }
    }

    public function deleteRecords(Request $request) {
        try {
            \DB::beginTransaction();
            $ids = $request->input('ids');
            $records_query = Media::withTrashed()
                ->whereIn('id', $ids);
                
            $records =  (clone $records_query)->get();
            
            $delete_files = [];
            foreach ($records as $record) {
                if($record->type == 1) { // video
                    $delete_files[] = [
                        'Key' => $record->gpx_path
                    ];
                }
                $delete_files[] = [
                    'Key' => $record->media_path
                ];
            }
            
            (clone $records_query)->forceDelete();
            $result = $this->s3service->deleteFiles($delete_files);

            \DB::commit();
            return back()->with(['success' => __("success_delete")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }

    public function preview(Request $request) {
        $admin = Auth::guard('admin')->user();
        $query = Media::query();
        if($admin->role == 2) {
            $query->whereHas('admin', function ($query) use($admin) {
                $query->where('group_id', $admin->group_id); 
            });
        }
        if($admin->role == 3) {
            $query->where('admin_id', $admin->id);
        }
        $records = $query->get();
        return Inertia::render('Admin/Media/Preview', [
            'records' => $records
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
}
