<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();
        
        $notifications = $user->notifications()->paginate(10);

        return view('panels.student.notifications', compact('student', 'notifications'));
    }
}
