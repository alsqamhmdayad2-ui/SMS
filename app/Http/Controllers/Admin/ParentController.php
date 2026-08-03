<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\User;
use App\Http\Requests\Parent\StoreParentRequest;
use App\Http\Requests\Parent\UpdateParentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $query = ParentModel::withCount('students')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('family_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_1', 'like', "%{$search}%");
            });
        }

        $parents = $query->paginate(20)->withQueryString();
        return view('panels.admin.parents.index', compact('parents'));
    }

    public function create()
    {
        return view('panels.admin.parents.create');
    }

    public function store(StoreParentRequest $request)
    {
        $data = $request->validated();
        $fullName = trim("{$data['first_name']} {$data['father_name']} {$data['grandfather_name']} {$data['family_name']}");

        DB::transaction(function () use ($data, $fullName) {
            $email = strtolower(str_replace(' ', '.', $data['first_name'])) . '.' . mt_rand(100, 999) . '@school.internal';

            $user = User::create([
                'name'        => $fullName,
                'email'       => $email,
                'national_id' => $data['national_id'],
                'password'    => Hash::make($data['national_id']),
            ]);
            $user->assignRole('parent');

            ParentModel::create([
                'user_id'          => $user->id,
                'full_name'        => $fullName,
                'first_name'       => $data['first_name'],
                'father_name'      => $data['father_name'],
                'grandfather_name' => $data['grandfather_name'],
                'family_name'      => $data['family_name'],
                'guardian_type'    => $data['guardian_type'],
                'national_id'      => $data['national_id'],
                'phone_1'          => $data['phone_1'],
                'phone_2'          => $data['phone_2'] ?? null,
                'occupation'       => $data['occupation'] ?? null,
                'workplace'        => $data['workplace'] ?? null,
                'address'          => $data['address'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم إضافة ولي الأمر بنجاح');
    }

    public function show(ParentModel $parent)
    {
        $parent->load('students.grade', 'students.schoolClass', 'students.section');
        return view('panels.admin.parents.show', compact('parent'));
    }

    public function edit(ParentModel $parent)
    {
        return view('panels.admin.parents.edit', compact('parent'));
    }

    public function update(UpdateParentRequest $request, ParentModel $parent)
    {
        $data = $request->validated();
        $fullName = trim("{$data['first_name']} {$data['father_name']} {$data['grandfather_name']} {$data['family_name']}");

        $parent->update([
            'full_name'        => $fullName,
            'first_name'       => $data['first_name'],
            'father_name'      => $data['father_name'],
            'grandfather_name' => $data['grandfather_name'],
            'family_name'      => $data['family_name'],
            'guardian_type'    => $data['guardian_type'],
            'national_id'      => $data['national_id'],
            'phone_1'          => $data['phone_1'],
            'phone_2'          => $data['phone_2'] ?? null,
            'occupation'       => $data['occupation'] ?? null,
            'workplace'        => $data['workplace'] ?? null,
            'address'          => $data['address'] ?? null,
        ]);

        if ($parent->user) {
            $parent->user->update(['name' => $fullName]);
        }

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم تعديل بيانات ولي الأمر بنجاح');
    }

    public function destroy(ParentModel $parent)
    {
        if ($parent->students()->exists()) {
            return back()->with('error', 'لا يمكن حذف ولي الأمر لأن لديه طلاب مرتبطون به.');
        }

        $parent->delete();

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'تم حذف ولي الأمر بنجاح');
    }
}
