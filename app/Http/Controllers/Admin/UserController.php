<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->when($request->get('search'), function ($query, $search) {
            return $query->where('name', 'LIKE', "%$search%");
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $data = $query->paginate($request->get('limit', 10));

        return Inertia::render('Admin/User/Index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/User/Create');
    }

    public function store(UserUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'gender', 'phone', 'address']);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $user = User::create($data);
        $message = sprintf('Successfully created %s', $user->name);

        return back()->with('success', $message);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/User/Edit', [
            'person' => $user
        ]);
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'gender', 'phone', 'address']);

        if(!isset($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

        return back()->with('success', __('success_update'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', __('success_delete'));
    }
}
