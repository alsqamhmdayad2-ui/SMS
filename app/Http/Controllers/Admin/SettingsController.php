<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::first() ?? new SchoolSetting();
        return view('panels.admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Identity
            'school_code'       => ['nullable', 'string', 'max:50'],
            'school_name'       => ['required', 'string', 'max:255'],
            'school_short_name' => ['nullable', 'string', 'max:100'],
            'school_name_en'    => ['nullable', 'string', 'max:255'],
            'logo'              => ['nullable', 'image', 'max:2048'],
            'academic_logo'     => ['nullable', 'image', 'max:2048'],
            'principal_name'    => ['nullable', 'string', 'max:255'],
            'principal_signature' => ['nullable', 'image', 'max:2048'],

            // Contact
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['nullable', 'email', 'max:255'],
            'website'     => ['nullable', 'url', 'max:255'],
            'address'     => ['nullable', 'string', 'max:500'],
            'country'     => ['nullable', 'string', 'max:100'],
            'city'        => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            // Academic
            'timezone'       => ['nullable', 'string', 'max:100'],
            'currency'       => ['nullable', 'string', 'max:10'],
            'academic_system' => ['nullable', 'string', 'max:50'],
            'grading_system'  => ['nullable', 'string', 'max:50'],
            'report_footer'   => ['nullable', 'string', 'max:1000'],
        ]);

        $settings = SchoolSetting::firstOrNew(['id' => 1]);

        // Handle file uploads
        foreach (['logo', 'academic_logo', 'principal_signature'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($settings->$field) {
                    Storage::disk('public')->delete($settings->$field);
                }
                $validated[$field] = $request->file($field)->store("school/{$field}", 'public');
            } else {
                // Keep existing value
                unset($validated[$field]);
            }
        }

        $settings->fill($validated)->save();

        // Clear the cache so the changes reflect immediately
        cache()->forget('sys_settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
