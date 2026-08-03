<?php

namespace Database\Seeders;

use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            [
                'national_id'  => '123456789',
                'full_name'    => 'Ahmad Father',
                'email'        => 'parent1@school.com',
                'guardian_type'=> 'Father',
                'phone_1'      => '0501234567',
                'occupation'   => 'Engineer',
                'address'      => 'Gaza',
            ],
            [
                'national_id'  => '987654321',
                'full_name'    => 'Sara Father',
                'email'        => 'parent2@school.com',
                'guardian_type'=> 'Father',
                'phone_1'      => '0507654321',
                'occupation'   => 'Doctor',
                'address'      => 'Gaza',
            ],
        ];

        foreach ($parents as $data) {
            $parent = ParentModel::firstOrCreate(
                ['national_id' => $data['national_id']],
                $data
            );

            // Create login user if not already linked
            if (!$parent->user_id) {
                $user = User::firstOrCreate(
                    ['national_id' => $data['national_id']],
                    [
                        'name'     => $data['full_name'],
                        'email'    => $data['email'],
                        'password' => Hash::make($data['national_id']),
                    ]
                );

                if (!$user->hasRole('parent')) {
                    $user->assignRole('parent');
                }

                $parent->update(['user_id' => $user->id]);
            }
        }
    }
}

