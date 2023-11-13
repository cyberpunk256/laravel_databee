<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'role' => 1,
            'group_id' => 1,
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('qweqweqwe'),
            'pref' => 13,
            'init_lat' => config('constant.map.init_pos.lat'),
            'init_long' => config('constant.map.init_pos.long'),
        ]);
        Admin::create([
            'role' => 2,
            'group_id' => 1,
            'name' => 'group',
            'email' => 'group@gmail.com',
            'password' => Hash::make('qweqweqwe'),
            'pref' => 13,
            'init_lat' => config('constant.map.init_pos.lat'),
            'init_long' => config('constant.map.init_pos.long'),
        ]);
        Admin::create([
            'role' => 3,
            'group_id' => 1,
            'name' => 'normal',
            'email' => 'normal@gmail.com',
            'password' => Hash::make('qweqweqwe'),
            'pref' => 13,
            'init_lat' => config('constant.map.init_pos.lat'),
            'init_long' => config('constant.map.init_pos.long'),
        ]);
    }
}
