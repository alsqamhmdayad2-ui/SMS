<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use Illuminate\Support\Facades\Hash;

class StudentParentUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Student User
        $studentUser = User::firstOrCreate(
            ['email' => 'student@school.com'],
            [
                'name' => 'محمد أحمد',
                'password' => Hash::make('password'),
            ]
        );

        if (!$studentUser->hasRole('student')) {
            $studentUser->assignRole('student');
        }

        // Link to first Student record
        $student = Student::first();
        if ($student) {
            $student->update(['user_id' => $studentUser->id]);
        }

        // Create Parent User
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@school.com'],
            [
                'name' => 'أحمد حسن',
                'password' => Hash::make('password'),
            ]
        );

        if (!$parentUser->hasRole('parent')) {
            $parentUser->assignRole('parent');
        }

        // Link to first Parent record
        $parent = ParentModel::first();
        if ($parent) {
            $parent->update(['user_id' => $parentUser->id]);
        }
    }
}
