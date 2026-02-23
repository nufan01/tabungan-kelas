<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mengecek apakah email sudah terdaftar agar tidak terjadi duplikat
        User::updateOrCreate(
            ['email' => 'walas@tabungankelas.com'],
            [
                'name' => 'Wali Kelas',
                'password' => Hash::make('T4bun94nK3l4s*123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
