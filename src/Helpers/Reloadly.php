<?php

namespace OTIFSolutions\LaravelAirtime\Helpers;

use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\LaravelAirtime\Models\{ReloadlyGiftCardTransaction,
    ReloadlyOperator,
    ReloadlyTransaction,
    ReloadlyUtilityTransaction};

/**
 * Class Reloadly
 * @package OTIFSolutions\LaravelAirtime
 */
class Reloadly {

    /**
     * @param null $key
     * @param null $secret
     * @param string $mode
     * @return object
     */
    public static function Make($key = null, $secret = null, $mode = 'LIVE'): object {

        return new class($key, $secret, $mode) {

            private $mode;
            private $key;
            private $secret;
            private $token;
            private $gift_token;
            private $utility_token;

            /**
             *  constructor.
             * @param $key
             * @param $secret
             * @param $mode
             */
            public function __construct($key, $secret, $mode) {
                $this->key = $key;
                $this->secret = $secret;
                $this->mode = $mode;
            }

            /**
             * @return string
             */
            private function getApiUrl(): string {
                return $this->mode === 'LIVE' ? 'https://topups.reloadly.com' : 'https://topups-sandbox.reloadly.com';
            }

            /**
             * @return string
             */
            public function getGiftCardApiUrl(): string{
                return $this->mode === 'LIVE' ?'https://giftcards.reloadly.com':'https://giftcards-sandbox.reloadly.com';
            }

            /**
             * @return string
             */
            public function getUtilityApiUrl(): string{
                return $this->mode === 'LIVE' ?'https://utilities.reloadly.com':'https://utilities-sandbox.reloadly.com';
            }

            /**
             * @param $key
             * @param $secret
             * @param string $mode
             * @return object
             */
            public function setCredentials($key, $secret, $mode = 'LIVE'): object {
                $this->key = $key;
                $this->secret = $secret;
                $this->mode = $mode;
                return $this;
            }

            /**
             * @param bool $enable
             * @return object
             */
            public function enableSandbox(bool $enable = true): object {
                $this->mode = $enable ? 'TEST' : 'LIVE';
                return $this;
            }

            public function setToken(string $token): object {
                $this->token = $token;
                return $this;
            }

            /**
             * @return string|null
             */
            public function getToken(): ?string {
                $response = Curl::Make()->POST->url("https://auth.reloadly.com/oauth/token")->header([
                    "Content-Type:application/json"
                ])->body([
                    'client_id' => $this->key,
                    'client_secret' => $this->secret,
                    'grant_type' => 'client_credentials',
                    'audience' => $this->getApiUrl()
                ])->execute();
                $this->token = $response['access_token'] ?? null;

                return $this->token;
            }

            public function setGiftToken(string $gift_token): object {
                $this->gift_token = $gift_token;
                return $this;
            }

            public function getGiftToken(): ?string {
                $response = Curl::Make()->POST->url("https://auth.reloadly.com/oauth/token")->header([
                    "Content-Type:application/json"
                ])->body([
                    'client_id' => $this->key,
                    'client_secret' => $this->secret,
                    'grant_type' => 'client_credentials',
                    'audience' => $this->getGiftCardApiUrl()
                ])->execute();
                $this->gift_token = isset($response['access_token'])? $response['access_token']:null;
                return $this->gift_token;
            }


            public function setUtilityToken(string $utility_token): object {
                $this->utility_token = $utility_token;
                return $this;
            }

            public function getUtilityToken(): ?string {
                $response = Curl::Make()->POST->url("https://auth.reloadly.com/oauth/token")->header([
                    "Content-Type:application/json"
                ])->body([
                    'client_id' => $this->key,
                    'client_secret' => $this->secret,
                    'grant_type' => 'client_credentials',
                    'audience' => $this->getUtilityApiUrl()
                ])->execute();
                $this->utility_token = isset($response['access_token'])?$response['access_token']:null;
                return $this->utility_token;
            }

            /**
             * @return array
             */
            public function getCountries(): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/countries")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();
            }

            /**
             * @param int $page
             * @return array
             */
            public function getOperators($page = 1): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/operators?page=$page&size=200&includeBundles=true&includeData=true&includePin=true&simplified=false&suggestedAmounts=true&suggestedAmountsMap=true")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();
            }

            /**
             * @param int $page
             * @return array
             */
            public function getOperatorsDiscount($page = 1): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/operators/commissions?page=$page&size=200&includeBundles=true&includeData=true&includePin=true&simplified=false&suggestedAmounts=true&suggestedAmountsMap=true")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();
            }

            /**
             * @return array
             */
            public function getBalance(): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/accounts/balance")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();
            }

            /**
             * @param $phone
             * @param $iso
             * @return ReloadlyOperator|null
             */
            public function autoDetectOperator($phone, $iso): ?ReloadlyOperator {
                $response = Curl::Make()->GET->url($this->getApiUrl() . "/operators/auto-detect/phone/$phone/country-code/" . $iso . "?&includeBundles=true")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();

                return isset($response['operatorId']) ? ReloadlyOperator::where('rid', $response['operatorId'])->first() : null;
            }

            /**
             * @param int $page
             * @return array
             */
            public function getPromotions($page = 1): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/promotions?page=$page")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer " . $this->token
                ])->execute();
            }

            /**
             * @param ReloadlyTransaction $transaction
             * @return bool
             */
            public function sendTopup(ReloadlyTransaction $transaction): bool {
                if (isset($transaction['operator']['country'])) {
                    $transaction['response'] = Curl::Make()->POST->url($this->getApiUrl() . "/topups")->header([
                        "Content-Type:application/json",
                        "Authorization: Bearer " . $this->token
                    ])->body([
                        'recipientPhone' => [
                            'countryCode' => $transaction['operator']['country']['iso'],
                            'number' => $transaction['number']
                        ],
                        'operatorId' => $transaction['operator']['rid'],
                        'amount' => $transaction['is_local'] ? $transaction['topup'] : $transaction['topup'] / $transaction['operator']['fx_rate'],
                        'useLocalAmount' => $transaction['is_local'] ? "true" : "false"
                    ])->execute();
                    if (isset($transaction['response']['transactionId']) && $transaction['response']['transactionId'] !== null && $transaction['response']['transactionId'] !== '') {
                        $transaction['status'] = 'SUCCESS';
                        if (isset($transaction['response']['pinDetail']))
                            $transaction['pin'] = $transaction['response']['pinDetail'];
                        $transaction->save();
                        return true;
                    }

                    $transaction['status'] = 'FAIL';
                    $transaction->save();
                    return false;
                }

                $transaction['status'] = 'FAIL';
                $transaction['response'] = [
                    'error' => 'OPERATOR/COUNTRY Not Found'
                ];
                $transaction->save();
                return false;
            }

            public function getReloadlyGiftProducts($page=1){
                return Curl::Make()->GET->url($this->getGiftCardApiUrl() ."/products?page=$page&size=200")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer ".$this->gift_token
                ])->execute();
            }

            public function orderReloadlyGiftProducts($rid, $iso, $quantity, $price, $identifier, $senderName, $email){
                return Curl::Make()->POST->url($this->getGiftCardApiUrl()."/orders")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer ".$this->gift_token
                ])->body([
                    'productId' => $rid,
                    'countryCode' => $iso,
                    'quantity' => $quantity,
                    'unitPrice' => $price,
                    'customIdentifier' => $identifier,
                    'senderName' => $senderName,
                    'recipientEmail' => $email
                ])->execute();
            }

            public function getReloadlyGiftTransaction(ReloadlyGiftCardTransaction $transaction)
            {
                if (isset($transaction['transaction_id'])) {
                    $response = Curl::Make()->GET->url($this->getGiftCardApiUrl() . "/reports/transactions/" . $transaction['transaction_id'])->header([
                        "Content-Type:application/json",
                        "Authorization: Bearer " . $this->gift_token
                    ])->execute();
                    if (isset($response['status'])) {
                        if($response['status'] === 'SUCCESSFUL') {
                            $transaction['transaction_id'] = $response['transactionId'];
                            $transaction['status'] = 'SUCCESS';
                        }else if($response['status'] === 'PENDING') {
                            $transaction['transaction_id'] = $response['transactionId'];
                            $transaction['status'] = 'PROCESSING';
                        }
                    } else {
                        $transaction['status'] = 'FAIL';
                    }
                    $transaction['response'] = $response;
                    $transaction->save();
                }
            }

            public function getReloadlyGiftRedeemCode(ReloadlyGiftCardTransaction $transaction)
            {
                if (isset($transaction['transaction_id'])) {
                    return Curl::Make()->GET->url($this->getGiftCardApiUrl() . "/orders/transactions/" . $transaction['transaction_id'].'/cards')->header([
                        "Content-Type:application/json",
                        "Authorization: Bearer " . $this->gift_token
                    ])->execute();
                }
            }

            public function getReloadlyUtilities($page=1){
                return Curl::Make()->GET->url($this->getUtilityApiUrl() ."/billers?page=$page&size=200")->header([
                    "Content-Type:application/json",
                    "Authorization: Bearer ".$this->utility_token
                ])->execute();
            }

            public function payUtilityBill(ReloadlyUtilityTransaction $transaction): bool {
                if (isset($transaction['utility_biller']['id'])) {
                    $transaction['response'] = Curl::Make()->POST->url($this->getUtilityApiUrl() . "/pay")->header([
                        "Content-Type:application/json",
                        "Authorization: Bearer " . $this->utility_token
                    ])->body([
                        'subscriberAccountNumber' => $transaction['subscriber_account_number'],
                        'billerId' => $transaction['utility_biller']['rid'],
                        'amount' => $transaction['is_local'] ? $transaction['amount'] : $transaction['amount'] / $transaction['fx_rate'],
                        'useLocalAmount' => $transaction['is_local'] ? "true" : "false",
                        'referenceId' => $transaction['reference_id']
                    ])->execute();
                    if (isset($transaction['response']['status']) && $transaction['response']['id'] !== null && $transaction['response']['id'] !== '') {
                        $transaction['t_id'] = $transaction['response']['id'];
                        $transaction['status'] = $transaction['response']['status'];
                        $transaction['reference_id'] = $transaction['response']['referenceId'];
                        $transaction['code'] = $transaction['response']['code'];
                        $transaction['message'] = $transaction['response']['message'];
                        $transaction->save();
                        return true;
                    }

                    $transaction['status'] = 'FAIL';
                    $transaction->save();
                    return false;
                }

                $transaction['status'] = 'FAIL';
                $transaction['response'] = [
                    'error' => 'UTILITY BILLER Not Found'
                ];
                $transaction->save();
                return false;
            }

            public function confirmReloadlyUtilityTransaction(ReloadlyUtilityTransaction $transaction)
            {
                if (isset($transaction['t_id'])) {
                    $transaction['response'] = Curl::Make()->GET->url($this->getUtilityApiUrl() . "/transactions/".$transaction['t_id'])->header([
                        "Content-Type:application/json",
                        "Authorization: Bearer " . $this->utility_token
                    ])->execute();
                    $transaction['code'] = $transaction['response']['code'];
                    $transaction['message'] = $transaction['response']['message'];
                    if (isset($transaction['response']['transaction']) && $transaction['response']['transaction']['status'] == 'SUCCESSFUL') {
                        $transactionResponse = $transaction['response']['transaction'];
                        $transaction['status'] = $transactionResponse['status'];
                        $transaction['balance_info'] = $transactionResponse['balanceInfo'];
                        $transaction['biller_details'] = $transactionResponse['billDetails'];
                        $transaction['amount_currency_code'] = $transactionResponse['amountCurrencyCode'];
                        $transaction['delivery_amount'] = $transactionResponse['deliveryAmount'];
                        $transaction['delivery_amount_currency_code'] = $transactionResponse['deliveryAmountCurrencyCode'];
                        $transaction['fee'] = $transactionResponse['fee'];
                        $transaction['fee_currency_code'] = $transactionResponse['feeCurrencyCode'];
                        $transaction['discount'] = $transactionResponse['discount'];
                        $transaction['discount_currency_code'] = $transactionResponse['discountCurrencyCode'];
                        $transaction['submitted_at'] = $transactionResponse['submittedAt'];
                        $transaction->save();
                        return true;
                    }
                    $transaction['status'] = 'FAIL';
                    $transaction->save();
                    return false;
                }

                $transaction['status'] = 'FAIL';
                $transaction['response'] = [
                    'error' => 'UTILITY BILLER Not Found'
                ];
                $transaction->save();
                return false;
            }

        };
    }
}
