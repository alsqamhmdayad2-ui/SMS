<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name'        => 'مدير النظام',
                'national_id' => 'ADMIN001',
                'password'    => Hash::make('password'),
            ]
        );

        // Update national_id if missing (for existing records)
        if (!$admin->national_id) {
            $admin->update(['national_id' => 'ADMIN001']);
        }

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
