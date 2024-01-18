<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\AWSService;

use App\Models\Media;

class IndexController extends Controller
{
    protected AWSService $aws_service;
    public function __construct()
    {
        $this->aws_service = new AWSService();
    }

    public function index(Request $request)
    {
        $user = Auth()->user();
        $records = Media::query()
            ->whereIn('status', [null, 1])
            ->whereNull('job_id')
            ->whereHas('admin', function ($query) use($user) {
                $query->where('pref', $user->pref); 
            })
            ->get();

        return Inertia::render('Index', [
            'records' => $records
        ]);
    }
}
