<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;


class HelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $helpersPath = base_path('app/Helpers');
        $files = File::files($helpersPath);

        foreach ($files as $file) {
            require_once $file;
        }
    }

    public function boot()
    {
       
    }
}
