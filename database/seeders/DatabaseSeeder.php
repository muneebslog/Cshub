<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('TEACHER_EMAIL', 'teacher@example.com')],
            [
                'name' => env('TEACHER_NAME', 'Teacher'),
                'password' => Hash::make(env('TEACHER_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ],
        );
    }
}
