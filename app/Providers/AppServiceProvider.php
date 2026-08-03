<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\App\Models\AttendanceSession::class, \App\Policies\AttendancePolicy::class);

        // Share school settings globally with all views
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('school_settings')) {
                $sysSettings = cache()->remember('sys_settings', 60*24, function() {
                    return \App\Models\SchoolSetting::first();
                });
                view()->share('sysSettings', $sysSettings);
            }
        } catch (\Exception $e) {
            // Ignore if DB not ready
        }
    }
}
