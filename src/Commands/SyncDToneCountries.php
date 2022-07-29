<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use OTIFSolutions\LaravelAirtime\Models\DToneCountry;

class SyncDToneCountries extends Command {

    protected $signature = 'sync:dt-json-countries';

    protected $description = 'sync countries reading from json';

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
        // $this->line('+++++++++++++++++++++++++++++++++++++++++++++++++++++++++');

        $progressBar->start();
        foreach ($countries as $country) {
            if (isset($country['tshop_id'])) {
                DToneCountry::updateOrCreate(['iso2' => $country['iso2']], [
                    'name' => $country['name'],
                    'dial_code' => $country['dialCode'],
                    't_shop_id' => $country['tshop_id']
                ]);
            }

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
