<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = User::query()->when($request->get('search'), function ($query, $search) {
            return $query->where('name', 'LIKE', "%$search%");
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $data = $query->paginate($request->get('limit', 10));

        return Inertia::render('User/Index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return Inertia::render('User/Create');
    }

    public function store(UserUpdateRequest $request)
    {
        $data = $request->only(['name', 'email', 'password', 'gender', 'phone', 'address']);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $user = User::create($data);
        $message = sprintf('Successfully created %s', $user->name);

        return redirect()->back()->with('success', $message);
    }

    public function edit(User $user)
    {
        return Inertia::render('User/Edit', [
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

        return redirect()->back()->with('success', __('success_update'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', __('success_delete'));
    }
}
