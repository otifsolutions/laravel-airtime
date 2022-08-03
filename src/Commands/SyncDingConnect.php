<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\DingConnect;
use OTIFSolutions\LaravelAirtime\Models\Currency;
use OTIFSolutions\LaravelAirtime\Models\DingConnectCountry;
use OTIFSolutions\LaravelAirtime\Models\DingConnectOperator;
use OTIFSolutions\LaravelAirtime\Models\DingConnectProduct;

class SyncDingConnect extends Command {

    protected $signature = 'sync:ding-connect';

    protected $description = 'Sync data with Ding-connect service';

    public function handle() {

        if (!Setting::get('ding_connect_service')) {
            $this->line("****************************************************************");
            $this->info("Ding-connect service is Diabled or false. Enable it first");
            $this->line("****************************************************************");
            return 0;
        }

        $this->line('Running migrations for Ding-Connect Service');
        $this->line('+++++++++++++++++++++++++++++++++++++++++++');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_27_084249_create_ding_connect_countries_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_27_084329_create_ding_connect_operators_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_27_084348_create_ding_connect_products_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_27_084418_create_ding_connect_transactions_table.php');
        $this->line('++++++++++++++++++++++++++++++++++++++++++++');

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Started Sync of DingConnect Operators");
        $this->line("****************************************************************");

        $this->line("Checking if credentials exist in database");

        $credentials = [
            'token' => Setting::get('ding_connect_token'),
        ];

        if (!$credentials['token']) {
            $this->error('Keys not found in settings.');
            return 0;
        }

        // --------- sycning countries form json------------------------------

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

        //  ----------------- syncing countries for ding-connect service ends

        $this->info("Credentials Found.");

        $this->line("Syncing Balance");
        $balance = DingConnect::Make($credentials['token'])->getBalance();
        Setting::set('ding_connect_balance', $balance, 'STRING');
        $this->info("Balance Synced.");

        $this->line("Syncing Operators/Countries");
        $countryResponses = DingConnect::Make($credentials['token'])->getCountries()['Items'];

        $this->line("");
        $this->line("*********************************************************************");
        $this->info("Soft Deleting All Countries to Sync only Active ones");
        $this->line("*********************************************************************");

        if (count($countryResponses)) {
            DingConnectCountry::whereNull('deleted_at')->delete();
        }

        $this->withProgressBar($countryResponses, function ($countryResponse) {
            $country = DingConnectCountry::withTrashed()->where('iso2', $countryResponse['CountryIso'])->first();
            if ($country === null) {
                DingConnectCountry::updateOrCreate([
                    'name' => $countryResponse['CountryName'],
                    'iso2' => $countryResponse['CountryIso'],
                    'dial_code' => @$countryResponse['InternationalDialingInformation'][0]['Prefix'] ?? ''
                ]);
            } else if ($country['deleted_at'])
                $country->restore();
        });

        $operatorResponses = DingConnect::Make($credentials['token'])->getProviders()['Items'];
        $this->line("");
        $this->info(count($operatorResponses) . " Operator(s) Found.");

        $this->line("");
        $this->line("*********************************************************************");
        $this->info("Soft Deleting All Operators and Products to Sync only Active ones");
        $this->line("*********************************************************************");

        if (count($countryResponses)) {
            DingConnectOperator::whereNull('deleted_at')->delete();
            DingConnectProduct::whereNull('deleted_at')->delete();
        }

        $this->line("Syncing ...");
        $this->withProgressBar($operatorResponses, function ($operatorResponse) {
            $country = DingConnectCountry::withTrashed()->where('iso2', $operatorResponse['CountryIso'])->first();
            if ($country === null) {
                $country = DingConnectCountry::updateOrCreate([
                    'name' => '',
                    'iso2' => $operatorResponse['CountryIso'],
                    'dial_code' => ''
                ]);
            } else if ($country['deleted_at'])
                $country->restore();

            DingConnectOperator::withTrashed()->updateOrCreate(
                ['name' => $operatorResponse['Name']], [
                    'country_id' => $country['id'],
                    'provider_code' => $operatorResponse['ProviderCode'],
                    'validation_regex' => $operatorResponse['ValidationRegex'],
                    'customer_care_no' => $operatorResponse['CustomerCareNumber'] ?? "",
                    'region_code' => $operatorResponse['RegionCodes'],
                    'payment_type' => $operatorResponse['PaymentTypes'],
                    'logo_url' => $operatorResponse['LogoUrl'] ?? "",
                    'deleted_at' => NULL
                ]
            );
        });

        $benefits = ['Mobile', 'Minutes', 'Data', 'Electricity', 'TV', 'Internet', 'Utility'];

        $this->line("");
        $this->line("Syncing Products");

        foreach ($benefits as $benefit) {
            $productResponses = DingConnect::Make($credentials['token'])->getProducts(null, null, $benefit)['Items'];
            $this->line("");
            $this->line("Getting Products For " . $benefit . " Category");
            $this->info(count($productResponses) . " Product(s) Found.");
            $category = 'Airtime';
            if (($benefit == 'Minutes') || ($benefit == 'Mobile'))
                $category = 'Airtime';
            elseif ($benefit == 'Data')
                $category = 'Data';
            elseif (($benefit == 'Electricity') || ($benefit == 'TV') || ($benefit == 'Internet') || ($benefit == 'Utility'))
                $category = 'Bill Payment';
            $this->line("Syncing ...");

            $this->withProgressBar($productResponses, function ($productResponse) use ($category) {
                $operator = DingConnectOperator::where('provider_code', $productResponse['ProviderCode'])->first();
                $currency = Currency::where('code', $productResponse['Maximum']['SendCurrencyIso'])->first();
                $destinationCurrency = Currency::where('code', $productResponse['Maximum']['ReceiveCurrencyIso'])->first();

                try {
                    DingConnectProduct::withTrashed()->updateOrCreate([
                        'operator_id' => $operator['id'],
                        'sku_code' => $productResponse['SkuCode']
                    ],
                        [
                            'country_id' => $operator['country_id'],
                            'category_name' => $category,
                            'currency_id' => $currency['id'],
                            'destination_currency_id' => $destinationCurrency['id'],
                            'fx_rate' => $productResponse['Minimum']['ReceiveValue'] / $productResponse['Minimum']['SendValue'],
                            'local_maximum_value' => $productResponse['Maximum']['ReceiveValue'],
                            'local_minimum_value' => $productResponse['Minimum']['ReceiveValue'],
                            'localization_key' => $productResponse['LocalizationKey'],
                            'maximum' => $productResponse['Maximum'],
                            'maximum_value' => $productResponse['Maximum']['SendValue'],
                            'minimum' => $productResponse['Minimum'],
                            'minimum_value' => $productResponse['Minimum']['SendValue'],
                            'commission_rate' => $productResponse['CommissionRate'],
                            'benefits' => $productResponse['Benefits'],
                            'uat_number' => $productResponse['UatNumber'],
                            'default_display_text' => $productResponse['DefaultDisplayText'],
                            'region_code' => $productResponse['RegionCode'],
                            'payment_types' => $productResponse['PaymentTypes'],
                            'lookup_bills_required' => $productResponse['LookupBillsRequired'],
                            'deleted_at' => NULL
                        ]
                    );
                } catch (\Exception $ex) {

                }
            });
        }

        $this->line(" ");
        $this->line("****************************************************************");
        $this->info("All DingConnect Operators Synced !!! ");
        $this->line("****************************************************************");
        $this->line("");

        return 0;
    }
}
