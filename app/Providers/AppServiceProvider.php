<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        try {
            $setting = Setting::first();
            View::share('setting', $setting);

            if (!app()->runningInConsole()) {
                $permissions = \Illuminate\Support\Facades\Cache::remember('permissions_with_roles', now()->addHours(24), function () {
                    return \App\Models\Permission::with('roles')->get();
                });

                foreach ($permissions as $permission) {
                    \Illuminate\Support\Facades\Gate::define($permission->slug, function (\App\Models\User $user) use ($permission) {
                        return $user->role_id ? $permission->roles->contains('id', $user->role_id) : false;
                    });
                }
            }
        } catch (\Exception $e) {
            // database tidak ditemukan
        }
    }
}
