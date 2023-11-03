<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Http\Requests\Admin\MediaUpdateRequest;

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
        );
    }
}
