<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUpdateRequest;
use App\Services\AWSService;

use App\Models\Media;
use App\Models\Setting;
use App\Jobs\MediaConvertJob;

class MediaController extends Controller
{
    protected AWSService $aws_service;
    public function __construct()
    {
        $this->aws_service = new AWSService();
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
        if($limit < 0) $limit = PHP_INT_MAX;
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
            return back()->with("error", __('success_error'));
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
                'encoding'
            ]);

            $data = [
                'admin_id' => $admin->id,
                'name' => $input['name'],
                'type' => $input['type'],
                'image_lat' => $input['image_lat'],
                'image_long' => $input['image_long'],
            ];

            if($input['type'] == 1) {
                if($input['encoding'] == 1) { // encoding
                    $data['media_path'] = "convert/" . $input['video']['file_full_name'];
                    $data['video_duration'] = $input['video']['video_duration'];
                    $data['gpx_path'] = "main/" . $input['gpx']['file_full_name'];
                    $this->aws_service->move($input['video']['file_path'], $data['media_path']);
                    $this->aws_service->move($input['gpx']['file_path'], $data['gpx_path']);
                    $record = Media::create($data);
                    $outputPrefix = "main/" . $input['video']['file_name'];
                    MediaConvertJob::dispatch($record, $outputPrefix)->onQueue('default');
                } else {
                    $data['media_path'] = "main/" . $input['video']['file_full_name'];
                    $data['video_duration'] = $input['video']['video_duration'];
                    $data['gpx_path'] = "main/" . $input['gpx']['file_full_name'];
                    $this->aws_service->move($input['video']['file_path'], $data['media_path']);
                    $this->aws_service->move($input['gpx']['file_path'], $data['gpx_path']);
                    $record = Media::create($data);
                }
            } else {
                $data['queue'] = 1; // complete : not meida convert
                $data['media_path'] = "main/" . $input['image']['file_full_name'];
                $this->aws_service->move($input['image']['file_path'], $data['media_path']);
                Media::create($data);
            }

            return redirect()->route('admin.media.index')->with(['success' => __("success_save")]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with("error", __('success_error'));
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
                'encoding'
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
                    if($input['encoding'] == 1) { // encoding
                        $data['media_path'] = "convert/" . $input['video']['file_full_name'];
                        $data['video_duration'] = $input['video']['video_duration'];
                        $this->aws_service->move($input['video']['file_path'], $data['media_path']);
                        if($record->media_path) {
                            $delete_files[] = [ 'Key' => $record->media_path ];
                        }
                        $outputPrefix = "main/" . $input['video']['file_name'];
                        MediaConvertJob::dispatch($record, $outputPrefix)->onQueue('default');
                    } else {
                        $data['media_path'] = "main/" . $input['video']['file_full_name'];
                        $data['video_duration'] = $input['video']['video_duration'];
                        $data['status'] = null;
                        $this->aws_service->move($input['video']['file_path'], $data['media_path']);
                        if($record->media_path) {
                            $delete_files[] = [ 'Key' => $record->media_path ];
                        }
                    }
                }
                if(isset($input['gpx'])) {
                    $data['gpx_path'] = "main/" . $input['gpx']['file_full_name'];
                    $this->aws_service->move($input['gpx']['file_path'], $data['gpx_path']);
                    if($record->gpx_path) {
                        $delete_files[] = [ 'Key' => $record->gpx_path ];
                    }
                }
            } else {
                if(isset($input['image'])) {
                    $data['media_path'] = "main/" . $input['image']['file_full_name'];
                    $this->aws_service->move($input['image']['file_path'], $data['media_path']);
                    if($record->media_path) {
                        $delete_files[] = [ 'Key' => $record->media_path ];
                    }
                }
            }

            $record->update($data);
            $this->aws_service->deleteFiles($delete_files);

            \DB::commit();
            return redirect()->route('admin.media.index')->with(['success' => __("success_update")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return back()->with("error", __('success_error'));
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
            $result = $this->aws_service->deleteFiles($delete_files);

            \DB::commit();
            return back()->with(['success' => __("success_delete")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return back()->with("error", __('success_error'));
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
            $file_name = $this->generateName();
            $file_full_name = $file_name . "." . $file_extension;
            $file_path = "tmp/" . $file_full_name;
            
            $presignedUrl = $this->aws_service->createPresignedUrl($file_path);
            return response()->json([
                "success" => __("success_complete"),
                'presigned_url' => $presignedUrl,
                'file_path' => $file_path,
                'file_full_name' => $file_full_name,
                'file_name' => $file_name
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with("error", __('success_error'));
        }
    }


    public function bulkUpload()
    {
        // $job = ["AccelerationSettings" => ["Mode" => "DISABLED"],"AccelerationStatus" => "NOT_APPLICABLE","Arn" => "arn => aws => mediaconvert => ap-northeast-1 => 465712026250 => jobs/1705548063858-f2se2d","BillingTagsSource" => "JOB","ClientRequestToken" => "cf6b9104-4168-4588-ace6-05f6843749dc","CreatedAt" => "2024-01-18 03 => 21 => 03","Id" => "1705548063858-f2se2d","Messages" => ["Info" => [],"Warning" => []],"OutputGroupDetails" => [["OutputDetails" => [["DurationInMs" => 30964,"VideoDetails" => ["HeightInPx" => 1080,"WidthInPx" => 2160]],["DurationInMs" => 30964,"VideoDetails" => ["HeightInPx" => 720,"WidthInPx" => 1440]],["DurationInMs" => 30964,"VideoDetails" => ["HeightInPx" => 480,"WidthInPx" => 960]]]]],"Priority" => 0,"Queue" => "arn => aws => mediaconvert => ap-northeast-1 => 465712026250 => queues/Default","Role" => "arn => aws => iam =>  => 465712026250 => role/service-role/MediaConvert_Default_Role","Settings" => ["FollowSource" => 1,"Inputs" => [["AudioSelectors" => ["Audio Selector 1" => ["DefaultSelection" => "DEFAULT"]],"FileInput" => "s3 => //3d-videos-new/convert/20240109/121748000000846.mp4","TimecodeSource" => "ZEROBASED","VideoSelector" => []]],"OutputGroups" => [["Name" => "Apple HLS","OutputGroupSettings" => ["HlsGroupSettings" => ["Destination" => "s3 => //3d-videos-new/main/20240118/122922000000527/index","MinSegmentLength" => 0,"SegmentLength" => 10],"Type" => "HLS_GROUP_SETTINGS"],"Outputs" => [["AudioDescriptions" => [["CodecSettings" => ["AacSettings" => "Over 9 levels deep, aborting normalization","Codec" => "Over 9 levels deep, aborting normalization"]]],"ContainerSettings" => ["Container" => "M3U8","M3u8Settings" => []],"NameModifier" => "_1080","OutputSettings" => ["HlsSettings" => []],"VideoDescription" => ["CodecSettings" => ["Codec" => "H_264","H264Settings" => ["MaxBitrate" => "Over 9 levels deep, aborting normalization","RateControlMode" => "Over 9 levels deep, aborting normalization","SceneChangeDetect" => "Over 9 levels deep, aborting normalization"]],"Height" => 1080]],["AudioDescriptions" => [["CodecSettings" => ["AacSettings" => "Over 9 levels deep, aborting normalization","Codec" => "Over 9 levels deep, aborting normalization"]]],"ContainerSettings" => ["Container" => "M3U8","M3u8Settings" => []],"NameModifier" => "_720","OutputSettings" => ["HlsSettings" => []],"VideoDescription" => ["CodecSettings" => ["Codec" => "H_264","H264Settings" => ["MaxBitrate" => "Over 9 levels deep, aborting normalization","RateControlMode" => "Over 9 levels deep, aborting normalization","SceneChangeDetect" => "Over 9 levels deep, aborting normalization"]],"Height" => 720]],["AudioDescriptions" => [["CodecSettings" => ["AacSettings" => "Over 9 levels deep, aborting normalization","Codec" => "Over 9 levels deep, aborting normalization"]]],"ContainerSettings" => ["Container" => "M3U8","M3u8Settings" => []],"NameModifier" => "_480","OutputSettings" => ["HlsSettings" => []],"VideoDescription" => ["CodecSettings" => ["Codec" => "H_264","H264Settings" => ["MaxBitrate" => "Over 9 levels deep, aborting normalization","RateControlMode" => "Over 9 levels deep, aborting normalization","SceneChangeDetect" => "Over 9 levels deep, aborting normalization"]],"Height" => 480]]]]],"TimecodeConfig" => ["Source" => "ZEROBASED"]],"Status" => "COMPLETE","StatusUpdateInterval" => "SECONDS_60","Timing" => ["FinishTime" => "2024-01-18 03 => 21 => 31","StartTime" => "2024-01-18 03 => 21 => 05","SubmitTime" => "2024-01-18 03 => 21 => 03"],"UserMetadata" => []];

        // dd($job['OutputGroupDetails'][0]['OutputDetails'][0]['DurationInMs']);
        // $record = Media::find(14);
        // $outputPrefix = "main/20240118/122922000000527";
        // MediaConvertJob::dispatch($record, $outputPrefix)->onQueue('default');
        // $queue = $this->mediaConvertClient->getQueue([
        //     'Name' => 'Default',
        // ]);
        // dd($queue);
        // $endPoint = $this->mediaConvertClient->describeEndpoints();
        $upload_path = Setting::where('key', 's3_upload_folder')->first();
        return Inertia::render('Admin/Media/BulkUpload', [
            'upload_path' => $upload_path->value
        ]);
    }
    
    public function bulkUploadStore()
    {
        $upload_names = [];
        try {
            $admin = Auth::guard('admin')->user();

            $setting = Setting::where("key", "s3_upload_folder")->first();
            if(!$setting) {
                return back()->with([
                    'error' => "アップロードファイルに関する設定がありません。"
                ], 404);
            }
            $folder = $setting->value;

            $gpx_list = $this->aws_service->getNameList($folder, ".gpx");
            $mp4_list = $this->aws_service->getNameList($folder, ".mp4");

            // get list of same name files
            $same_name_list = array_intersect($gpx_list, $mp4_list);

            foreach($same_name_list as $name) {
                $file_name = $this->generateName();
                $data = [
                    'admin_id' => $admin->id,
                    'name' => $name,
                    'type' => 1,
                    'image_lat' => null,
                    'image_long' => null,
                    'media_path' => "convert/" . $file_name . ".mp4",
                    "video_duration" => null,
                    "gpx_path" => "main/" . $file_name . ".gpx",
                ];
                $video_file_path = $folder . "/" . $name . ".mp4";
                $gpx_file_path = $folder . "/" . $name . ".gpx";
                $this->aws_service->move($video_file_path, $data['media_path']);
                $this->aws_service->move($gpx_file_path, $data['gpx_path']);
                $record = Media::create($data);
                $outputPrefix = "main/" . $file_name;
                MediaConvertJob::dispatch($record, $outputPrefix)->onQueue('default');
                $upload_names[] = $file_name . ".mp4";
                $upload_names[] = $file_name . ".gpx";
            }
            return back()->with([
                'success' => __("success_upload"),
                'data' => $upload_names,
            ]);
        } catch (\Throwable $exception) {
            \Log::error($exception);
            return back()->with([
                'error' => __("success_error"),
                'data' => $upload_names,
            ]);
        }
    }

    public function generateName() {
       return date('Ymd') . '/'. date('Hisu')  . '_'. mt_rand(100, 999);
    }
}
