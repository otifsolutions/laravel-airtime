<?php

namespace OTIFSolutions\LaravelAirtime\AirtimeServiceProvider;

use Illuminate\Support\ServiceProvider;
use OTIFSolutions\LaravelAirtime\Commands\SyncReloadly;
use OTIFSolutions\LaravelAirtime\Commands\SyncValueTopup;
use OTIFSolutions\LaravelAirtime\Commands\SyncValueTopupStatus;

class LaravelAirtimeServiceProvider extends ServiceProvider {

    public function register() {

    }

    public function boot() {
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations/');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncValueTopup::class,
                SyncValueTopupStatus::class,
                SyncReloadly::class
            ]);
        }

    }
}
