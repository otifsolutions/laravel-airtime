<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\DTone;
use OTIFSolutions\LaravelAirtime\Models\Currency;
use OTIFSolutions\LaravelAirtime\Models\DToneCountry;
use OTIFSolutions\LaravelAirtime\Models\DToneOperator;
use OTIFSolutions\LaravelAirtime\Models\DToneProduct;

class SyncDTone extends Command {

    protected $signature = 'sync:d-tone';

    protected $description = 'Sync the data with d-tone platform';

    public function handle() {
        $this->line("");
        $this->line("****************************************************************");
        $this->info("Started Sync of DTone");
        $this->line("****************************************************************");

        $this->line("Checking if credentials exist in database");
        $credentials = [
            'name' => Setting::get('dtone_tshop_username'),
            'token' => Setting::get('dtone_tshop_token'),
        ];
        if (!$credentials['name'] || !$credentials['token']) {
            $this->error('Keys not found in settings.');
            return 0;
        }
        $this->info("Credentials Found.");

        $this->line("Syncing Balance");
        $balance = DTone::TShop($credentials['name'], $credentials['token'])->getBalance();
        Setting::set('d_tone_balance', $balance, 'STRING');
        $this->info("Balance Synced.");

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Soft Deleting All Operators to Sync only Active ones");
        $this->line("****************************************************************");

        DToneOperator::whereNull('deleted_at')->delete();

        $this->line("Started Sync of DTone Operators");
        $countries = DToneCountry::all();
        $this->withProgressBar($countries, function ($country) use ($credentials) {
            $operatorResponses = DTone::TShop($credentials['name'], $credentials['token'])->getOperators($country);
            if (isset($operatorResponses['operator'], $operatorResponses['operatorid'])) {
                $operatorNames = explode(",", $operatorResponses['operator']);
                $operatorIds = explode(",", $operatorResponses['operatorid']);
                foreach ($operatorNames as $i => $operatorName) {
                    if (!empty($operatorName) && !empty($operatorIds[$i])) {
                        $type = 'Airtime';
                        if (str_contains(strtoupper($operatorName), 'GIFTCARD'))
                            $type = 'Gift Card';
                        DToneOperator::withTrashed()->updateOrCreate(
                            ['t_shop_id' => $operatorIds[$i]],
                            [
                                't_shop_id' => $operatorIds[$i],
                                'country_id' => $country['id'],
                                'name' => $operatorName,
                                'type' => $type,
                                'deleted_at' => NULL
                            ]
                        );
                    }

                }
            }
        });
        $this->line(" ");
        $this->info("All DTone Operators Synced !!! ");

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Soft Deleting All Products to Sync only Active ones");
        $this->line("****************************************************************");

        DToneProduct::whereNull('deleted_at')->delete();

        $this->line("Started Sync of DTone Products");
        $operators = DToneOperator::all();
        $this->withProgressBar($operators, function ($operator) use ($credentials) {
            $productResponses = DTone::TShop($credentials['name'], $credentials['token'])->getProducts($operator['t_shop_id']);
            $senderCurrencyId = Setting::get('dtone_currency_id');
            if (isset($productResponses['product_list'], $productResponses['retail_price_list'], $productResponses['wholesale_price_list'])) {
                $productNames = explode(",", $productResponses['product_list']);
                $retailPrices = explode(",", $productResponses['retail_price_list']);
                $wholesalePrices = explode(",", $productResponses['wholesale_price_list']);
                foreach ($productNames as $i => $productName) {
                    $currency = Currency::where('code', $productResponses['destination_currency'])->first();
                    if (!$currency) {
                        $currency = Currency::create([
                            'code' => $productResponses['destination_currency'],
                            'base_currency_id' => 1,
                            'rate' => 0,
                            'profit' => 0,
                            'status' => 'DISABLED'
                        ]);
                    }
                    DToneProduct::withTrashed()->updateOrCreate(
                        [
                            'product' => $productName,
                            'operator_id' => $operator['id']
                        ],
                        [
                            'country_id' => $operator['country_id'],
                            'sender_currency_id' => $senderCurrencyId,
                            'destination_currency_id' => $currency['id'],
                            'retail_price' => $retailPrices[$i],
                            'wholesale_price' => $wholesalePrices[$i],
                            'deleted_at' => NULL
                        ]
                    );
                }
            }
        });

        $this->line(" ");
        $this->info("All DTone Products Synced !!! ");

        $this->line(" ");
        $this->line("****************************************************************");
        $this->info("All Done !!! ");
        $this->line("****************************************************************");
        $this->line("");
        return 0;

    }
}
