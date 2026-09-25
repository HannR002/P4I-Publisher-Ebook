<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@p4ipublisher.com'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );
    }
}
