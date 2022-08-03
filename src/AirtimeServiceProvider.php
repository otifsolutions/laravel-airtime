<?php

namespace OTIFSolutions\LaravelAirtime;

use Illuminate\Support\ServiceProvider;
use OTIFSolutions\LaravelAirtime\Commands\{SyncDingConnect,
    SyncDTone,
    SyncReloadly,
    SyncValueTopup,
    SyncValueTopupStatus
};

class AirtimeServiceProvider extends ServiceProvider {

    public function register() {

    }

    public function boot() {
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations/');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncValueTopup::class,
                SyncValueTopupStatus::class,
                SyncReloadly::class,
                SyncDingConnect::class,
                SyncDTone::class
            ]);
        }

    }
}
