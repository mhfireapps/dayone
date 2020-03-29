<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ScriptTagService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SCRIPT_TAG_SERVICE', function ($app) {
            return new ScriptTagService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
