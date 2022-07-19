<?php

namespace OTIFSolutions\LaravelAirtime\AirtimeServiceProvider;

use Illuminate\Support\ServiceProvider;
use OTIFSolutions\LaravelAirtime\Commands\SyncReloadly;

class LaravelAirtimeServiceProvider extends ServiceProvider {

    public function register() {

    }

    public function boot() {
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations/');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncReloadly::class
            ]);
        }

    }
}
