<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\ValueTopup;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupCategory;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupCountry;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupOperator;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupProduct;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupPromotion;

class SyncValueTopup extends Command {

    protected $signature = 'sync:value-topoup';

    protected $description = 'Sync countries,operators,products with the ValueTopup Platform';

    public function handle() {

        if (!Setting::get('value_topup_service')) {
            $this->line("****************************************************************");
            $this->info("Value-topup service is Diabled or false. Enable it first");
            $this->line("****************************************************************");
            return 0;
        }

        $this->info('Running migrations for Value-topup service');
        $this->line('+++++++++++++++++++++++++++++++++++++++++++++++++++');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133006_create_value_topup_categories_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133040_create_value_topup_countries_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133104_create_value_topup_products_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133130_create_value_topup_promotions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133154_create_value_topup_transactions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_21_133231_create_value_topup_operators_table.php');
        $this->line('+++++++++++++++++++++++++++++++++++++++++++++++++++');




        $this->line("");
        $this->line("****************************************************************");
        $this->info("Getting token to authenticate from ValueTopup Platform");
        $this->line("****************************************************************");

        $this->line("Fetching Balance");
        $balance = ValueTopup::Make()->getBalance();

        if (($balance['responseCode'] === "000") && isset($balance['payLoad']['balance'])) {
            Setting::set('value_topup_balance', $balance['payLoad']['balance'], 'STRING');
            $this->info("Balance Synced");
        } else {
            $this->info("Balance API Failed");
            return 0;
        }

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Started Sync of Products with ValueTopup Platform");
        $this->line("****************************************************************");
        $this->line("Fetching Products list from ValueTopup");

        $categories = ValueTopupCategory::all();
        $countries = ValueTopupCountry::all();
        $carriers = ValueTopup::Make()->getValueTopupCarrier();

        $this->info("Fetching Complete.");
        $this->line("Syncing with database.");
        foreach ($carriers['payLoad'] as $carrier) {
            $valueTopupCategory = $categories->where('name', $carrier['category'])->first();
            if (!$valueTopupCategory) {
                $valueTopupCategory = ValueTopupCategory::updateOrCreate(
                    [
                        'name' => $carrier['category']
                    ]
                );
            }
            $valueTopupCountry = $countries->where('country_code', $carrier['countryCode'])->where('category_id', $valueTopupCategory['id'])->first();
            if (!$valueTopupCountry) {
                $valueTopupCountry = ValueTopupCountry::updateOrCreate(
                    [
                        'country_code' => $carrier['countryCode'],
                        'category_id' => $valueTopupCategory['id']
                    ]
                );
            }

            ValueTopupOperator::updateOrCreate(
                ['product_id' => $carrier['productId'], 'country_id' => $valueTopupCountry['id']],
                [
                    'carrier_name' => $carrier['carrierName'],
                    'denomination_type' => $carrier['denominationType']
                ]
            );
        }

        $categories = ValueTopupCategory::all();
        $countries = ValueTopupCountry::all();
        $operators = ValueTopupOperator::all();

        $products = ValueTopup::Make()->getValueTopupProducts();
        $this->info("Fetching Products details.");
        $this->line("Syncing with database.");

        foreach ($products['payLoad'] as $product) {
            $valueTopupCategory = $categories->where('name', $product['category'])->first();
            $valueTopupCountry = $countries->where('country_code', $product['countryCode'])->where('category_id', $valueTopupCategory['id'])->first();
            $valueTopupOperator = $operators->where('product_id', $product['productId'])->where('country_id', $valueTopupCountry['id'])->first();
            ValueTopupProduct::updateOrCreate(
                ['sku_id' => $product['skuId']],
                [
                    'operator_id' => $valueTopupOperator['id'],
                    'product_id' => $product['productId'],
                    'product_name' => $product['productName'],
                    'face_value' => $product['faceValue'],
                    'min_amount' => $product['minAmount'],
                    'max_amount' => $product['maxAmount'],
                    'discount' => $product['discount'],
                    'pricing' => $product['pricing'],
                    'category' => $product['category'],
                    'is_sales_tax_charged' => $product['isSalesTaxCharged'],
                    'sales_tax' => $product['salesTax'],
                    'exchange_rate' => $product['exchangeRate'],
                    'currency_code' => $product['currencyCode'],
                    'country_code' => $product['countryCode'],
                    'local_phone_number_length' => $product['localPhoneNumberLength'],
                    'international_country_code' => $product['internationalCountryCode'],
                    'allow_decimal' => $product['allowDecimal'],
                    'fee' => $product['fee'],
                    'operator_name' => $product['operatorName'],
                    'delivery_currency_code' => $product['deliveryCurrencyCode'],
                    'supported_transaction_currencies' => $product['supportedTransactionCurrencies'],
                    'carrier_name' => $product['carrierName']
                ]
            );
        }

        $productsDescription = ValueTopup::Make()->getValueTopupProductsDescription();
        $this->info("Fetching Description of Products.");
        $this->line("Syncing with database.");

        foreach ($productsDescription['payLoad'] as $productDescription) {
            $valueTopupProduct = ValueTopupProduct::where('sku_id', $productDescription['skuId'])->first();
            if ($valueTopupProduct)
                ValueTopupProduct::updateOrCreate(
                    ['sku_id' => $productDescription['skuId']],
                    [
                        'description' => $productDescription['description']
                    ]
                );
        }

        $operatorsLogo = ValueTopup::Make()->getValueTopupOperatorLogo();
        $this->info("Fetching Logo of Operators.");
        $this->line("Syncing with database.");

        foreach ($operatorsLogo['payLoad'] as $operatorsLogo) {
            ValueTopupOperator::updateOrCreate(
                ['product_id' => $operatorsLogo['productId']],
                [
                    'image_url' => $operatorsLogo['imageUrl']
                ]
            );
        }

        $currentPromotions = ValueTopup::Make()->getValueTopupCurrentPromotion();
        $this->info("Fetching current promotions of Operators.");
        $this->line("Syncing with database.");

        $this->syncPromotions($currentPromotions);

        $upcomingPromotions = ValueTopup::Make()->getValueTopupUpcomingPromotion();
        $this->info("Fetching upcoming promotions of Operators.");
        $this->line("Syncing with database.");

        $this->syncPromotions($upcomingPromotions);

        $this->info("Syncing Country Names");

        $jsonCountries = json_decode(file_get_contents(__DIR__ . '../../Database/jsons/countriesValueTopup.json'), false, 512, JSON_THROW_ON_ERROR);

        foreach ($jsonCountries as $jsonCountry) {
            $countries = ValueTopupCountry::whereNull('name')->where('country_code', $jsonCountry->code)->get();
            if ($countries) {
                foreach ($countries as $country) {
                    $country['name'] = $jsonCountry->name;
                    $country->save();
                }
            }
        }

        $this->line("****************************************************************");
        $this->info("Sync Complete");
        $this->line("****************************************************************");
        $this->line("");

    }

    private function syncPromotions($promotions) {
        foreach ($promotions['payLoad'] as $promotion) {
            $valueTopupOperator = ValueTopupOperator::where('product_id', $promotion['product']['productId'])->first();
            ValueTopupPromotion::updateOrCreate(
                ['name' => $promotion['promotionName']],
                [
                    'operator_id' => $valueTopupOperator['id'],
                    'start_date' => $promotion['startDate'],
                    'end_date' => $promotion['endDate'],
                    'description' => $promotion['description'],
                    'restriction' => $promotion['restriction'],
                    'promotion_min_max' => $promotion['promotionMinMax'],
                    'product' => $promotion['product'],
                ]
            );
        }
    }
}
