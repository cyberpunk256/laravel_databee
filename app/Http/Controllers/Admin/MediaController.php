<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Models\Media;

class MediaController extends Controller
{
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

    public function getPresignedUrl(Request $request)
    {
        $fileName = $request->input('file_name'); // ファイル名を取得する

        $s3 = Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => 'uploads/' . $fileName,
        ]);

        $request = $client->createPresignedRequest($cmd, '+20 minutes');

        return response()->json([
            'presigned_url' => (string) $request->getUri(),
        ]);
    }
}
