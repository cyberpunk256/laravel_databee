<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Database\Factories\UserFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => Hash::make('qweqweqwe'),
            'email_verified_at' => now(),
            'pref' => 13,
            'init_lat' => config('constant.map.init_pos.lat'),
            'init_long' => config('constant.map.init_pos.long'),
        ]);
        UserFactory::new()->count(100)->create();
    }
}
