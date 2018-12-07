<?php
namespace Jorge\Rebrandly;

use Illuminate\Support\ServiceProvider;

class RebrandlyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/rebrandly.php" => config_path('rebrandly.php'),
        ]);
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('rebrandly', function () {
            return new Rebrandly;
        });
    }
}
