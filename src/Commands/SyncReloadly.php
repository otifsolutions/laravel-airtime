<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\Reloadly;
use OTIFSolutions\LaravelAirtime\Models\{AirtimeCurrency,
    ReloadlyCountry,
    ReloadlyDiscount,
    ReloadlyGiftCardProduct,
    ReloadlyOperator,
    ReloadlyPromotion,
    ReloadlyUtility};

class SyncReloadly extends Command {

    protected $signature = 'sync:reloadly';

    protected $description = 'Sync data with the Reloadly platform';

    public function handle() {

        if (!Setting::get('reloadly_service')) {
            $this->line("****************************************************************");
            $this->info("Reloadly Service is NULL or false. Enable it first");
            $this->line("****************************************************************");
            return 0;
        }

        $this->line('Running migrations for Reloadly Service');
        $this->line('++++++++++++++++++++++++++++++++++++++++++++++');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_18_000002_create_reloadly_countries_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_18_000003_create_reloadly_operators_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_18_000004_create_reloadly_discounts_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_18_000005_create_reloadly_promotions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_18_000006_create_reloadly_transactions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_29_000006_create_reloadly_gift_card_products_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_29_000007_create_reloadly_gift_card_transactions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2023_05_03_084523_add_processing_status_to_reloadly_gift_card_transactions_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_29_000008_create_reloadly_utilities_table.php');
        Artisan::call('migrate --path=vendor/otifsolutions/laravel-airtime/src/Database/migrations/2022_07_29_000008_create_reloadly_utility_transactions_table.php');
        $this->line('++++++++++++++++++++++++++++++++++++++++++++++');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started Sync of Reloadly API');
        $this->line('****************************************************************');

        $this->line('Checking if credentials exist in database');

        $credentials = [
            'key' => Setting::get('reloadly_api_key'),
            'secret' => Setting::get('reloadly_api_secret'),
            'mode' => Setting::get('reloadly_api_mode')
        ];

        if (!$credentials['key'] || !$credentials['secret'] || !$credentials['mode']) {
            return $this->returnError('Keys not found in settings.');
        }

        $this->info('Credentials Found');
        $this->line('Generating a New Token to be used');
        $reloadly = Reloadly::Make($credentials['key'], $credentials['secret'], $credentials['mode']);
        $credentials['token'] = $reloadly->getToken();

        if (!$credentials['token']) {
            return $this->returnError('Unable to generate a successful token');
        }

        Setting::set('reloadly_api_token', $credentials['token']);

        $this->info('Token Updated/Saved to database');

        $this->line('Syncing Balance');
        $balance = $reloadly->getBalance();

        Setting::set('reloadly_balance', $balance['balance'], 'STRING');

        $this->info('Balance Synced.');
        $this->line('Fetching Countries list from Reloadly');

        $countries = $reloadly->getCountries();
        $this->info(count($countries) . ' Country(s) Found');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Soft Deleting All Countries to Sync only Active ones');
        $this->line('****************************************************************');

        if (count($countries)) {
            ReloadlyCountry::whereNotNull('deleted_at')->delete();
        }

        $this->line('Syncing with database.');
        $this->withProgressBar($countries, function ($country) {
            $currency = AirtimeCurrency::where('code', $country['currencyCode'])->first();
            if ($currency === null) {
                $currency = AirtimeCurrency::updateOrCreate([
                    'code' => $country['currencyCode'],
                    'base_currency_id' => 1,
                    'rate' => 0,
                    'profit' => 0
                ]);
            }

            ReloadlyCountry::withTrashed()->updateOrCreate(
                ['iso' => $country['isoName']], [
                    'name' => $country['name'],
                    'currency_id' => $currency['id'],
                    'currency_name' => $country['currencyName'],
                    'currency_symbol' => $country['currencySymbol'],
                    'flag' => $country['flag'],
                    'calling_codes' => $country['callingCodes'],
                    'deleted_at' => NULL
                ]
            );
        });

        $this->line(' ');
        $this->line('****************************************************************');
        $this->info('Sync Complete !!! ' . count($countries) . ' Countries Synced.');
        $this->line('****************************************************************');
        $this->line('');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started Sync of Operators with Reloadly Platform');
        $this->line('****************************************************************');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Soft Deleting All Operators to Sync only Active ones');
        $this->line('****************************************************************');

        ReloadlyOperator::whereNull('deleted_at')->delete();
        $page = 1;

        do {
            $this->line('Fetching Operators Page : ' . $page);
            $response = $reloadly->getOperators($page);
            $this->info('Fetch Success !!!');
            $page++;
            $this->line('Syncing with Database');
            if (isset($response['content'])){
                $this->withProgressBar($response['content'], function ($operator) {
                    if (isset($operator['operatorId'])) {
                        $senderCurrency = AirtimeCurrency::where('code', $operator['senderCurrencyCode'])->first();
                        if ($senderCurrency === null) {
                            $senderCurrency = AirtimeCurrency::updateOrCreate([
                                'code' => $operator['senderCurrencyCode'],
                                'base_currency_id' => 1,
                                'rate' => 0,
                                'profit' => 0
                            ]);
                        }

                        $destinationCurrency = AirtimeCurrency::where('code', $operator['destinationCurrencyCode'])->first();
                        if ($destinationCurrency === null) {
                            $destinationCurrency = AirtimeCurrency::updateOrCreate([
                                'code' => $operator['destinationCurrencyCode'],
                                'base_currency_id' => 1,
                                'rate' => 0,
                                'profit' => 0
                            ]);
                        }

                        ReloadlyOperator::withTrashed()->updateOrCreate(
                            ['rid' => $operator['operatorId']], [
                                'rid' => $operator['operatorId'],
                                'country_id' => ReloadlyCountry::where('iso', $operator['country']['isoName'])->first()['id'],
                                'name' => $operator['name'],
                                'bundle' => $operator['bundle'],
                                'data' => $operator['data'],
                                'pin' => $operator['pin'],
                                'supports_local_amounts' => $operator['supportsLocalAmounts'],
                                'denomination_type' => $operator['denominationType'],
                                'sender_currency_id' => $senderCurrency['id'],
                                'sender_currency_symbol' => $operator['senderCurrencySymbol'],
                                'destination_currency_id' => $destinationCurrency['id'],
                                'destination_currency_symbol' => $operator['destinationCurrencySymbol'],
                                'commission' => $operator['commission'],
                                'international_discount' => $operator['internationalDiscount'],
                                'local_discount' => $operator['localDiscount'],
                                'most_popular_amount' => $operator['mostPopularAmount'],
                                'min_amount' => $operator['minAmount'],
                                'local_min_amount' => $operator['localMinAmount'],
                                'max_amount' => $operator['maxAmount'],
                                'local_max_amount' => $operator['localMaxAmount'],
                                'fx_rate' => $operator['fx']['rate'],
                                'logo_urls' => $operator['logoUrls'],
                                'fixed_amounts' => $operator['fixedAmounts'],
                                'fixed_amounts_descriptions' => $operator['fixedAmountsDescriptions'],
                                'local_fixed_amounts' => $operator['localFixedAmounts'],
                                'local_fixed_amounts_descriptions' => $operator['localFixedAmountsDescriptions'] ?? [],
                                'suggested_amounts' => $operator['suggestedAmounts'],
                                'suggested_amounts_map' => $operator['suggestedAmountsMap'],
                                'deleted_at' => NULL
                            ]
                        );
                    }
                });
            }

            $this->line(' ');
            $this->info('Sync Completed For ' . count($response['content']) . ' Operators');

        } while (isset($response['totalPages']) && $response['totalPages'] >= $page);

        $this->line('****************************************************************');
        $this->info('All Operators Synced !!! ');
        $this->line('****************************************************************');
        $this->line('');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started Sync of Promotions with Reloadly Platform');
        $this->line('****************************************************************');
        $this->line('Removing all current promotions.');

        ReloadlyPromotion::truncate();
        $this->info('All Promotions Removed.');
        $page = 1;

        do {
            $this->line('Fetching Promotions Page : ' . $page);
            $response = $reloadly->getPromotions($page);
            $this->info('Fetch Success !!!');
            $page++;
            $this->line('Syncing with Database');
            if (isset($response['content'])){
                $this->withProgressBar($response['content'], function ($promotion) {
                    if (isset($promotion['promotionId'])) {
                        $operator = ReloadlyOperator::where('rid', $promotion['operatorId'])->first();
                        if ($operator) {
                            ReloadlyPromotion::updateOrCreate(
                                ['rid' => $promotion['promotionId']], [
                                    'rid' => $promotion['promotionId'],
                                    'operator_id' => $operator['id'],
                                    'title' => $promotion['title'],
                                    'title2' => $promotion['title2'],
                                    'description' => $promotion['description'],
                                    'start_date' => $promotion['startDate'],
                                    'end_date' => $promotion['endDate'],
                                    'denominations' => $promotion['denominations'],
                                    'local_denominations' => $promotion['localDenominations']
                                ]
                            );
                        }
                    }
                });
            }

            $this->line(' ');
            $this->info('Sync Completed For ' . count($response['content']) . ' Promotions');

        } while (isset($response['totalPages']) && $response['totalPages'] >= $page);

        $this->line('****************************************************************');
        $this->info('All Promotions Synced !!! ');
        $this->line('****************************************************************');
        $this->line('');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started Sync of Operators Discount with Reloadly Platform');
        $this->line('****************************************************************');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Soft Deleting All Discounts to Sync only Active ones');
        $this->line('****************************************************************');

        ReloadlyDiscount::whereNull('deleted_at')->delete();

        $page = 1;

        do {
            $this->line('Fetching Discounts Page : ' . $page);
            $response = $reloadly->getOperatorsDiscount($page);
            $this->info('Fetch Success !!!');
            $page++;
            $this->line('Syncing with Database');
            if (isset($response['content'])){
                $this->withProgressBar($response['content'], function ($discount) {
                    if (isset($discount['operator']['operatorId'])) {
                        $operator = ReloadlyOperator::where('rid', $discount['operator']['operatorId'])->first();
                        if ($operator) {
                            ReloadlyDiscount::withTrashed()->updateOrCreate(
                                ['rid' => $discount['operator']['operatorId']], [
                                    'rid' => $discount['operator']['operatorId'],
                                    'operator_id' => $operator['id'],
                                    'percentage' => $discount['percentage'],
                                    'international_percentage' => $discount['internationalPercentage'],
                                    'local_percentage' => $discount['localPercentage'],
                                    'updated_at' => $discount['updatedAt'],
                                    'deleted_at' => NULL
                                ]
                            );
                        }
                    }
                });
            }

            $this->line(' ');
            $this->info('Sync Completed For ' . count($response['content']) . ' Discounts');

        } while (isset($response['totalPages']) && $response['totalPages'] >= $page);

        $this->line('****************************************************************');
        $this->info('All Discounts Synced !!! ');
        $this->line('****************************************************************');
        $this->line('');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Soft Deleting All Reloadly Gift Cards to Sync only Active ones');
        $this->line('****************************************************************');

        ReloadlyGiftCardProduct::whereNull('deleted_at')->delete();


        $page = 1;
        $credentials['gift_card_token'] = $reloadly->getGiftToken();
        do {
            $this->line(' ');
            $this->line("Fetching Gift Products Page : ".$page);
            $response = $reloadly->getReloadlyGiftProducts($page);
            $this->info("Fetch Success !!!");
            $page++;
            $this->line("Syncing with Database");
            if (isset($response['content'])) {
                $this->withProgressBar($response['content'], function ($product) {
                    if (isset($product['productId'], $product['country']['isoName'])) {
                        $country = ReloadlyCountry::where('iso', $product['country']['isoName'])->first();
                        if (!$country) {
                            $currency = AirtimeCurrency::where('code', $product['recipientCurrencyCode'])->first();
                            if ($currency) {
                                $country = ReloadlyCountry::updateOrCreate(['iso' => $product['country']['isoName']], [
                                    'name' => $product['country']['name'],
                                    'flag' => $product['country']['flagUrl'],
                                    'currency_id' => $currency['id'],
                                    'currency_code' => $product['recipientCurrencyCode'],
                                    'currency_name' => $product['recipientCurrencyCode'],
                                    'currency_symbol' => $product['recipientCurrencyCode'],
                                    'calling_codes' => []
                                ]);
                            }
                        }
                        if ($country){
                            ReloadlyGiftCardProduct::withTrashed()->updateOrCreate(
                                ['rid' => $product['productId']],
                                [
                                    'rid' => $product['productId'],
                                    'country_id' => $country['id'],
                                    'title' => $product['productName'],
                                    'is_global' => $product['global'],
                                    'sender_fee' => $product['senderFee'],
                                    'discount_percentage' => $product['discountPercentage'],
                                    'denomination_type' => $product['denominationType'],
                                    'recipient_currency_code' => $product['recipientCurrencyCode'],
                                    'min_recipient_denomination' => $product['minRecipientDenomination'],
                                    'max_recipient_denomination' => $product['maxRecipientDenomination'],
                                    'sender_currency_code' => $product['senderCurrencyCode'],
                                    'min_sender_denomination' => $product['minSenderDenomination'],
                                    'max_sender_denomination' => $product['maxSenderDenomination'],
                                    'fixed_recipient_denominations' => $product['fixedRecipientDenominations'],
                                    'fixed_sender_denominations' => $product['fixedSenderDenominations'],
                                    'fixed_denominations_map' => $product['fixedRecipientToSenderDenominationsMap'],
                                    'logo_urls' => $product['logoUrls'],
                                    'brand' => $product['brand'],
                                    'country' => $product['country'],
                                    'redeem_instruction' => $product['redeemInstruction'],
                                    'deleted_at' => NULL
                                ]
                            );
                        }
                    }
                });
                $this->line(' ');
                $this->info("Sync Completed For ".count($response['content'])." Gift Products");
            }
        } while (isset($response['totalPages']) && $response['totalPages'] >= $page);

        $this->line('****************************************************************');
        $this->info('All Reloadly Gift Cards Synced !!! ');
        $this->line('****************************************************************');
        $this->line('');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Soft Deleting All Reloadly Utility Billers to Sync only Active ones');
        $this->line('****************************************************************');
        ReloadlyUtility::whereNull('deleted_at')->delete();
        $page = 1;
        $credentials['utility_token'] = $reloadly->getUtilityToken();
        do {
            $this->line(' ');
            $this->line("Fetching Utility Payments : ".$page);
            $response = $reloadly->getReloadlyUtilities($page);
            $this->info("Fetch Success !!!");
            $page++;
            $this->line("Syncing with Database");
            if (isset($response['content'])) {
                $this->withProgressBar($response['content'], function ($biller) {
                    if (isset($biller['id'], $biller['countryCode'])) {
                        $country = ReloadlyCountry::where('iso', $biller['countryCode'])->first();
                        if (!$country) {
                            $currency = AirtimeCurrency::where('code', $biller['localTransactionCurrencyCode'])->first();
                            if ($currency)
                                $country = ReloadlyCountry::updateOrCreate(['iso' => $biller['country']['isoName']], [
                                    'name' => $biller['country']['name'],
                                    'flag' => $biller['country']['flagUrl'],
                                    'currency_id' => $currency['id'],
                                    'currency_code' => $biller['recipientCurrencyCode'],
                                    'currency_name' => $biller['recipientCurrencyCode'],
                                    'currency_symbol' => $biller['recipientCurrencyCode'],
                                    'calling_codes' => []
                                ]);
                        }
                        if ($country){
                            ReloadlyUtility::withTrashed()->updateOrCreate(['rid' => $biller['id']],
                                [
                                    'rid' => $biller['id'],
                                    'country_id' => $country['id'],
                                    'name' => $biller['name'],
                                    'country_code' => $biller['countryCode'],
                                    'country_name' => $biller['countryName'],
                                    'type' => $biller['type'],
                                    'service_type' => $biller['serviceType'],
                                    'local_amount_supported' => $biller['localAmountSupported'],
                                    'local_transaction_currency_code' => $biller['localTransactionCurrencyCode'],
                                    'min_local_transaction_amount' => $biller['minLocalTransactionAmount'],
                                    'max_local_transaction_amount' => $biller['maxLocalTransactionAmount'],
                                    'local_transaction_fee' => $biller['localTransactionFee'],
                                    'local_transaction_fee_currency_code' => $biller['localTransactionFeeCurrencyCode'],
                                    'fx_rate' => @$biller['fx']['rate'],
                                    'fx_currency_code' => @$biller['fx']['currencyCode'],
                                    'local_discount_percentage' => $biller['localDiscountPercentage'],
                                    'international_amount_supported' => $biller['internationalAmountSupported'],
                                    'international_transaction_currency_code' => $biller['internationalTransactionCurrencyCode'],
                                    'min_international_transaction_amount' => $biller['minInternationalTransactionAmount'],
                                    'max_international_transaction_amount' => $biller['maxInternationalTransactionAmount'],
                                    'international_transaction_fee' => $biller['internationalTransactionFee'],
                                    'international_transaction_fee_currency_code' => $biller['internationalTransactionFeeCurrencyCode'],
                                    'international_discount_percentage' => $biller['internationalDiscountPercentage'],
                                    'deleted_at' => NULL
                                ]
                            );
                        }
                    }
                });
                $this->line(' ');
                $this->info("Sync Completed For ".count($response['content'])." Gift Products");
            }
        } while (isset($response['totalPages']) && $response['totalPages'] >= $page);

        return 0;
    }

    private function returnError(string $error): int {
        $this->error($error);
        return 0;
    }

}
