<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'national_id'      => '100000001',
                'first_name'       => 'Ahmad',
                'father_name'      => 'Mohammad',
                'grandfather_name' => 'Ali',
                'family_name'      => 'Math',
                'user_email'       => 'teacher1@school.com',   // used for User only
                'phone'            => '0551112222',
                'specialization'   => 'Mathematics',
                'address'          => 'Gaza',
            ],
            [
                'national_id'      => '100000002',
                'first_name'       => 'Sara',
                'father_name'      => 'Khaled',
                'grandfather_name' => 'Omar',
                'family_name'      => 'Science',
                'user_email'       => 'teacher2@school.com',   // used for User only
                'phone'            => '0553334444',
                'specialization'   => 'Science',
                'address'          => 'Gaza',
            ],
        ];

        foreach ($teachers as $data) {
            $userEmail = $data['user_email'];
            unset($data['user_email']); // remove before inserting to teachers table

            $teacher = Teacher::firstOrCreate(
                ['national_id' => $data['national_id']],
                $data
            );

            // Create login user if not already linked
            if (!$teacher->user_id) {
                $user = User::firstOrCreate(
                    ['national_id' => $data['national_id']],
                    [
                        'name'     => $data['first_name'] . ' ' . $data['family_name'],
                        'email'    => $userEmail,
                        'password' => Hash::make($data['national_id']),
                    ]
                );

                if (!$user->hasRole('teacher')) {
                    $user->assignRole('teacher');
                }

                $teacher->update(['user_id' => $user->id]);
            }
        }

        // Assign subjects to teachers
        $teacher1 = Teacher::where('national_id', '100000001')->first();
        $teacher2 = Teacher::where('national_id', '100000002')->first();

        $mathSubject = Subject::where('code', 'MATH101')->first();
        if ($mathSubject && $teacher1) {
            $teacher1->subjects()->syncWithoutDetaching([$mathSubject->id]);
        }

        $physics   = Subject::where('code', 'PHY101')->first();
        $chemistry = Subject::where('code', 'CHE101')->first();
        if ($teacher2) {
            $subjectIds = array_filter([$physics?->id, $chemistry?->id]);
            if ($subjectIds) {
                $teacher2->subjects()->syncWithoutDetaching($subjectIds);
            }
        }
    }
}
