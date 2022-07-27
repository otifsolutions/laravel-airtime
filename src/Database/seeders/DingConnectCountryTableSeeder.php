<?php

namespace OTIFSolutions\LaravelAirtime\Database\Seeders;

use Illuminate\Database\Seeder;
use OTIFSolutions\LaravelAirtime\Models\DingConnectCountry;

class DingConnectCountryTableSeeder extends Seeder {

    public function run() {
        $countries = json_decode(file_get_contents(__DIR__ . '/jsons/countriesValueTopup.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($countries as $country) {
            DingConnectCountry::updateOrCreate(['iso2' => $country['iso2']], [
                'name' => $country['name'],
                'dial_code' => $country['dialCode']
            ]);
        }
    }

}
