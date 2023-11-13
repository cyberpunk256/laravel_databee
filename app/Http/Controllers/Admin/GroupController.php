<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupUpdateRequest;
use App\Models\Group;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::query()->when($request->get('search'), function ($query, $search) {
            return $query->where('name', 'LIKE', "%$search%");
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $limit = $request->get('limit', 10);
        if($limit < 0) $limit = 0;
        $data = $query->paginate($limit);

        return Inertia::render('Admin/Group/Index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Group/Create');
    }

    public function store(GroupUpdateRequest $request)
    {
        $data = $request->only(['name']);
        Group::create($data);

        return back()->with(['success' => __("success_save")]);
    }

    public function edit(Group $group)
    {
        return Inertia::render('Admin/Group/Edit', [
            'person' => $group
        ]);
    }

    public function update(Group $group, GroupUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'gender', 'phone', 'address']);

        if(!isset($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $group->update($data);

        return back()->with('success', __('success_update'));
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return back()->with('success', __('success_delete'));
    }
}
