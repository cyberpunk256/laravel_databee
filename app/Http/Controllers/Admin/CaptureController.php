<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Services\S3Service;
use App\Models\Capture;
use App\Http\Requests\Admin\CaptureUpdateRequest;

class CaptureController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $query = Capture::query()
            ->select('*')
            ->with([
                'user' => function($sub_query) {
                    $sub_query->select('id', 'name');
                }
            ])
            ->when($request->get('search'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })->when($request->get('sort'), function ($query, $sortBy) {
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $limit = $request->get('limit', 10);
        if($limit < 0) $limit = 0;
        $data = $query->paginate($limit);

        return Inertia::render('Admin/Capture/Index', [
            'data' => $data
        ]);
    }

    public function destroy(Capture $capture)
    {
        try {
            \DB::beginTransaction();
            $delete_files[] = [
                'Key' => $capture->url
            ];

            $capture->delete();
            $result = $this->s3service->deleteFiles($delete_files);

            \DB::commit();
            return back()->with('success', __('success_delete'));
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return response()->json([
                "error" => __('success_error')
            ], 404);
        }
    }
}
