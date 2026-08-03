<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherService
{
    public function getAll()
    {
        return Teacher::with('user')->latest()->paginate(10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Generate a system email (internal use only)
            $email = strtolower(Str::ascii($data['first_name'] . '.' . $data['family_name'])) . mt_rand(100, 999) . '@school.internal';

            $user = User::create([
                'name'        => $data['first_name'] . ' ' . $data['family_name'],
                'email'       => $email,
                'national_id' => $data['national_id'],
                'password'    => Hash::make($data['national_id']), // Default password = national_id
            ]);

            // Assign teacher role
            $user->assignRole('teacher');

            $data['user_id'] = $user->id;

            $subjects = $data['subjects'] ?? [];
            unset($data['subjects']);

            $teacher = Teacher::create($data);
            
            if (!empty($subjects)) {
                $teacher->qualifiedSubjects()->sync($subjects);
            }

            return $teacher;
        });
    }

    public function update(Teacher $teacher, array $data)
    {
        return DB::transaction(function () use ($teacher, $data) {
            $subjects = $data['subjects'] ?? [];
            unset($data['subjects']);

            $teacher->update($data);

            if (isset($subjects)) {
                $teacher->qualifiedSubjects()->sync($subjects);
            }

            if ($teacher->user) {
                $teacher->user->update([
                    'name' => trim("{$data['first_name']} {$data['father_name']} {$data['grandfather_name']} {$data['family_name']}"),
                ]);
            }

            return $teacher;
        });
    }

    public function delete(Teacher $teacher)
    {
        return DB::transaction(function () use ($teacher) {
            if ($teacher->user) {
                $teacher->user->delete();
            }
            return $teacher->delete();
        });
    }
}
