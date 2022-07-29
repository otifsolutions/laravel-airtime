<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;

class SyncDToneCountries extends Command {

    protected $signature = 'sync:dt-json-countries';

    protected $description = 'sync countries reading from json';

    public function handle() {

        return 0;
    }
}
