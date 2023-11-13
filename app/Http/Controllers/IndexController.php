<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\S3Service;

use App\Models\Media;

class IndexController extends Controller
{
    protected S3Service $s3service;
    public function __construct()
    {
        $this->s3service = new S3Service();
    }

    public function index(Request $request)
    {
        $user = Auth()->user();
        $records = Media::query()
            ->whereHas('admin', function ($query) use($user) {
                $query->where('pref', $user->pref); 
            })
            ->get();

        return Inertia::render('Index', [
            'records' => $records
        ]);
    }
}
