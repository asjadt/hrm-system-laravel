<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Observers\AttendanceObserver;
use App\Observers\LeaveObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);
        User::observe(UserObserver::class);
        Attendance::observe(AttendanceObserver::class);

        Leave::observe(LeaveObserver::class);


    }
}
