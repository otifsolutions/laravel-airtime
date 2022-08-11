<?php

namespace OTIFSolutions\LaravelAirtime\Helpers;

use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\LaravelAirtime\Models\{ReloadlyOperator, ReloadlyTransaction};

/**
 * Class Reloadly
 * @package App\Classes
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
        };
    }
}
