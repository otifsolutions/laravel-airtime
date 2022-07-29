<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use OTIFSolutions\LaravelAirtime\Models\DingConnectCountry;
use Illuminate\Console\Command;

class SyncDingConnectCountries extends Command {

    protected $signature = 'sync:ding-connect-countries';

    protected $description = 'sync countries names and dial code from jsons';

    public function handle() {

        $countries = json_decode(
            file_get_contents(__DIR__ . '../../Database/jsons/countriesDingConnect.json'),
            true, 512, JSON_THROW_ON_ERROR
        );

        $countCoutnries = count($countries);

        $progressBar = $this->output->createProgressBar($countCoutnries);

        $this->newLine();
        $this->line('+++++++++++++++++++++++++++++++++++++++++++++++++++++++++');
        $this->info('>>>>>>>>> Syncing countries table with json <<<<<<<<<<<<<');

        $progressBar->start();
        foreach ($countries as $country) {
            DingConnectCountry::updateOrCreate(['iso2' => $country['iso2']], [
                'name' => $country['name'],
                'dial_code' => $country['dialCode']
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('>>>>>>>>>>>> ' . $countCoutnries . ' countries synced <<<<<<<<<<');
        $this->line('+++++++++++++++++++++++++++++++++++++++++++++++++++++++++');
        $this->newLine();

        return 0;
    }
}
