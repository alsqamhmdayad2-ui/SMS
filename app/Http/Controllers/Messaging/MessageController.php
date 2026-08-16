<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $messages = Message::whereHas('recipients', function($q) use ($user) {
                $q->where('receiver_id', $user->id);
            })
            ->whereNull('parent_id') // Show only main threads in inbox
            ->with(['sender', 'recipients' => function($q) use ($user) {
                $q->where('receiver_id', $user->id);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('panels.messages.index', compact('messages'));
    }

    public function sent()
    {
        $user = Auth::user();
        
        $messages = Message::where('sender_id', $user->id)
            ->whereNull('parent_id')
            ->with(['recipients.receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('panels.messages.sent', compact('messages'));
    }

    public function create()
    {
        $authUser = Auth::user();
        $sections = collect();
        $allowedUserIds = collect();

        if ($authUser->hasRole('admin')) {
            // Admin: can message everyone
            $users = User::where('id', '!=', $authUser->id)
                ->with('roles')->get()
                ->groupBy(fn($u) => $u->roles->first()->name ?? 'غير محدد');
            $sections = \App\Models\Section::with('schoolClass')->get();

        } elseif ($authUser->hasRole('teacher')) {
            // Teacher: can message admins, other teachers, and their own students
            $teacher = \App\Models\Teacher::where('user_id', $authUser->id)->first();
            $sectionIds = collect();
            if ($teacher) {
                $sectionIds = \Illuminate\Support\Facades\DB::table('subject_section_teacher')
                    ->where('teacher_id', $teacher->id)
                    ->pluck('section_id')
                    ->unique();
                $sections = \App\Models\Section::whereIn('id', $sectionIds)->with('schoolClass')->get();
            }
            $studentUserIds = \App\Models\Student::whereIn('section_id', $sectionIds)
                ->whereNotNull('user_id')->pluck('user_id');

            $users = User::where('id', '!=', $authUser->id)
                ->where(function($q) use ($studentUserIds) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['admin', 'teacher']))
                      ->orWhereIn('id', $studentUserIds);
                })
                ->with('roles')->get()
                ->groupBy(fn($u) => $u->roles->first()->name ?? 'غير محدد');

        } elseif ($authUser->hasRole('student')) {
            // Student: can only message their teachers and admins
            $student = \App\Models\Student::where('user_id', $authUser->id)->first();
            $teacherUserIds = collect();
            if ($student) {
                $teacherIds = \Illuminate\Support\Facades\DB::table('subject_section_teacher')
                    ->where('section_id', $student->section_id)
                    ->pluck('teacher_id')
                    ->unique();
                $teacherUserIds = \App\Models\Teacher::whereIn('id', $teacherIds)
                    ->whereNotNull('user_id')->pluck('user_id');
            }
            $users = User::where('id', '!=', $authUser->id)
                ->where(function($q) use ($teacherUserIds) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'admin'))
                      ->orWhereIn('id', $teacherUserIds);
                })
                ->with('roles')->get()
                ->groupBy(fn($u) => $u->roles->first()->name ?? 'غير محدد');

        } elseif ($authUser->hasRole('parent')) {
            // Parent: can only message their children's teachers and admins
            $parent = \App\Models\ParentModel::where('user_id', $authUser->id)->with('students')->first();
            $teacherUserIds = collect();
            if ($parent) {
                $sectionIds = $parent->students->pluck('section_id')->filter()->unique();
                $teacherIds = \Illuminate\Support\Facades\DB::table('subject_section_teacher')
                    ->whereIn('section_id', $sectionIds)
                    ->pluck('teacher_id')
                    ->unique();
                $teacherUserIds = \App\Models\Teacher::whereIn('id', $teacherIds)
                    ->whereNotNull('user_id')->pluck('user_id');
            }
            $users = User::where('id', '!=', $authUser->id)
                ->where(function($q) use ($teacherUserIds) {
                    $q->whereHas('roles', fn($r) => $r->where('name', 'admin'))
                      ->orWhereIn('id', $teacherUserIds);
                })
                ->with('roles')->get()
                ->groupBy(fn($u) => $u->roles->first()->name ?? 'غير محدد');

        } else {
            $users = collect();
        }

        return view('panels.messages.create', compact('users', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'recipients' => 'nullable|array',
            'recipients.*' => 'exists:users,id',
            'sections' => 'nullable|array',
            'sections.*' => 'exists:sections,id',
        ]);

        if (empty($request->recipients) && empty($request->sections)) {
            return back()->withErrors(['recipients' => 'يجب تحديد مستلم واحد أو شعبة واحدة على الأقل.'])->withInput();
        }

        $allRecipients = $request->recipients ?? [];

        if (!empty($request->sections)) {
            $studentUserIds = \App\Models\Student::whereIn('section_id', $request->sections)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();
            
            $allRecipients = array_merge($allRecipients, $studentUserIds);
        }

        $allRecipients = array_unique($allRecipients);

        if (empty($allRecipients)) {
            return back()->withErrors(['recipients' => 'لم يتم العثور على مستخدمين صالحين في الاختيارات المحددة.'])->withInput();
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        foreach ($allRecipients as $recipientId) {
            MessageRecipient::create([
                'message_id' => $message->id,
                'receiver_id' => $recipientId,
            ]);
        }

        return redirect()->route('messages.index')->with('success', 'تم إرسال الرسالة بنجاح.');
    }

    public function show($id)
    {
        $user = Auth::user();
        
        $message = Message::with(['sender', 'recipients', 'replies.sender'])->findOrFail($id);
        
        $isSender = $message->sender_id === $user->id;
        $isRecipient = $message->recipients->contains('receiver_id', $user->id);
        
        if (!$isSender && !$isRecipient) {
            abort(403);
        }

        // Mark main message as read
        if ($isRecipient) {
            $recipient = $message->recipients->where('receiver_id', $user->id)->first();
            if ($recipient && !$recipient->read_at) {
                $recipient->update(['read_at' => now()]);
            }
        }
        
        // Mark replies as read
        foreach($message->replies as $reply) {
            $replyRec = $reply->recipients->where('receiver_id', $user->id)->first();
            if ($replyRec && !$replyRec->read_at) {
                $replyRec->update(['read_at' => now()]);
            }
        }

        return view('panels.messages.show', compact('message'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $parentMessage = Message::findOrFail($id);
        
        $user = Auth::user();
        $isSender = $parentMessage->sender_id === $user->id;
        
        $reply = Message::create([
            'sender_id' => $user->id,
            'subject' => 'رد: ' . $parentMessage->subject,
            'body' => $request->body,
            'parent_id' => $parentMessage->id,
        ]);

        if ($isSender) {
            $receivers = $parentMessage->recipients->pluck('receiver_id');
        } else {
            $receivers = collect([$parentMessage->sender_id]);
        }

        foreach ($receivers as $receiverId) {
            MessageRecipient::create([
                'message_id' => $reply->id,
                'receiver_id' => $receiverId,
            ]);
        }

        return redirect()->route('messages.show', $parentMessage->id)->with('success', 'تم إرسال الرد بنجاح.');
    }
}
