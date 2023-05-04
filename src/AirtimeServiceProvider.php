<?php

namespace OTIFSolutions\LaravelAirtime;

use Illuminate\Support\ServiceProvider;
use OTIFSolutions\LaravelAirtime\Commands\{SyncDingConnect,
    SyncDTone,
    SyncReloadly,
    SyncReloadlyUtilityTransaction,
    SyncValueTopup,
    SyncValueTopupStatus};

class AirtimeServiceProvider extends ServiceProvider {

    public function boot() {

        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations/2022_07_18_000001_create_airtime_currencies_table.php');

        $this->commands([
            SyncValueTopup::class,
            SyncValueTopupStatus::class,
            SyncReloadly::class,
            SyncDingConnect::class,
            SyncDTone::class,
            SyncReloadlyUtilityTransaction::class
        ]);

    }
}
