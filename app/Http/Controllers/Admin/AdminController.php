<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Models\Admin;
use App\Models\Group;
use App\Services\AWSService;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // $aws_service = new AWSService();
        // $tmp_obj = $aws_service->getList("tmp")->toArray();
        // $tmp_list = $tmp_obj['Contents'];
        // dd($tmp_list);
        $query = Admin::query()
            ->select('*')
            ->with([
                'group' => function($sub_query) {
                    $sub_query->select('id', 'name');
                }
            ])->when($request->get('search'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })->when($request->get('sort'), function ($query, $sortBy) {
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $limit = $request->get('limit', 10);
        if($limit < 0) $limit = 0;
        $data = $query->paginate($limit);

        return Inertia::render('Admin/Admin/Index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        $groups = Group::query()->select('name', 'id')->get();
        return Inertia::render('Admin/Admin/Create',[
            'group_options' => $groups
        ]);
    }

    public function store(AdminUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'role', 'group_id', 'pref', 'init_lat','init_long']);
        Admin::create($data);

        return back()->with(['success' => __("success_save")]);
    }

    public function edit(Admin $admin)
    {
        $groups = Group::query()->select('name', 'id')->get();
        return Inertia::render('Admin/Admin/Edit', [
            'admin' => $admin,
            'group_options' => $groups
        ]);
    }

    public function update(Admin $admin, AdminUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'role', 'group_id', 'pref', 'init_lat', 'init_long']);

        if(!isset($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $admin->update($data);

        return back()->with('success', __('success_update'));
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return back()->with('success', __('success_delete'));
    }
}
