<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Admin seeding is prohibited in production. Use an approved account recovery procedure.');
        }
        $email = env('DEVELOPMENT_ADMIN_EMAIL');
        $password = env('DEVELOPMENT_ADMIN_PASSWORD');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Explicit development admin credentials are required (password minimum 12 characters).');
        }
        if (User::where('email', $email)->exists()) {
            throw new RuntimeException('Existing account will not be overwritten or promoted by a seeder.');
        }
        User::create(['email' => $email, 'name' => 'Development Administrator', 'password' => Hash::make($password), 'is_admin' => true]);
    }
}
