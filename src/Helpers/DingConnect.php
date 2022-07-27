<?php

namespace OTIFSolutions\LaravelAirtime\Helpers;

use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\LaravelAirtime\Models\DingConnectOperator;
use OTIFSolutions\LaravelAirtime\Models\DingConnectTransaction;

class DingConnect {

    public static function Make($apiKey = null): object {

        return new class($apiKey) {

            private $apiKey;
            private $url = "https://www.dingconnect.com/api/V1/";

            public function __construct($apiKey) {
                $this->apiKey = $apiKey;
            }

            public function setCredentials($apiKey): object {
                $this->apiKey = $apiKey;
                return $this;
            }

            public function getBalance(): ?string {
                $response = Curl::Make()->GET->url($this->url . "GetBalance")->header([
                    'api_key: ' . $this->apiKey
                ])->execute();

                return $response['Balance'] ?? null;
            }

            public function getCountries() {
                return Curl::Make()->GET->url($this->url . "GetCountries")->params([
                ])->header([
                    'api_key: ' . $this->apiKey
                ])->execute();
            }

            public function getProviders($countryCode = null, $providerCode = null) {
                return Curl::Make()->GET->url($this->url . "GetProviders")->params([
                    'countryIsos' => $countryCode,
                    'providerCodes' => $providerCode
                ])->header([
                    'api_key: ' . $this->apiKey
                ])->execute();
            }

            public function getProducts($countryCode = null, $providerCode = null, $benefits = null) {
                return Curl::Make()->GET->url($this->url . "GetProducts")->params([
                    'countryIsos' => $countryCode,
                    'providerCodes' => $providerCode,
                    'benefits' => $benefits
                ])->header([
                    'api_key: ' . $this->apiKey
                ])->execute();
            }

            public function getAccountInformation($number) {
                $response = Curl::Make()->GET->url($this->url . "GetAccountLookup")->params([
                    'accountNumber' => $number
                ])->header([
                    'api_key: ' . $this->apiKey
                ])->execute();
                if (isset($response['Items'][0]['ProviderCode'])) {
                    $operator = DingConnectOperator::where('provider_code', $response['Items'][0]['ProviderCode'])->first();
                    if ($operator)
                        return $operator;
                }
                return null;
            }

            public function sendTransfer(DingConnectTransaction $transaction) {
                $number = $transaction['number'];
                if (($transaction['product']['category_name'] !== "Bill Payment") && (strpos($number, "0") === 0)) {
                    $number = substr($number, 1);
                }
                $code = $transaction['operator']['country']['dial_code'];
                if ((strpos($number, $code) === 0)) {
                    $number = substr($number, strlen($code));
                }
                if (strpos($number, "+" . $code) === 0) {
                    $number = substr($number, strlen("+" . $code));
                }

                $body = [
                    "SkuCode" => $transaction['sku_code'],
                    "SendValue" => $transaction['send_value'],
                    "SendCurrencyIso" => $transaction['send_currency_code'],
                    "AccountNumber" => $number,
                    "DistributorRef" => $transaction['ref'],
                    "ValidateOnly" => false
                ];

                if ($transaction['product']['lookup_bills_required']) {
                    $response = Curl::Make()->POST->url($this->url . "LookupBills")->header([
                        'api_key: ' . $this->apiKey,
                        "Content-Type: application/json"
                    ])->body([
                        "SkuCode" => $transaction['sku_code'],
                        "AccountNumber" => $number,
                    ])->execute();
                    if (isset($response['Items'][0]['BillRef'])) {
                        $body['BillRef'] = $response['Items'][0]['BillRef'];
                    }
                }

                $transaction['response'] = Curl::Make()->POST->url($this->url . "SendTransfer")->header([
                    'api_key: ' . $this->apiKey,
                    "Content-Type: application/json"
                ])->body($body)->execute();
                if (isset($transaction['response']['TransferRecord']['TransferId'], $transaction['response']['TransferRecord']['ProcessingState']) && $transaction['response']['TransferRecord']['ProcessingState'] !== 'Failed') {
                    $transaction['status'] = 'SUCCESS';
                    $transaction->save();
                    return true;
                }

                $transaction['status'] = 'FAIL';
                $transaction->save();
                return false;
            }

            public function lookUpBill(DingConnectTransaction $transaction) {
                $number = $transaction['number'];
                $code = $transaction['operator']['country']['dial_code'];
                if ((strpos($number, $code) === 0)) {
                    $number = substr($number, strlen($code));
                }
                if (strpos($number, "+" . $code) === 0) {
                    $number = substr($number, strlen("+" . $code));
                }

                return Curl::Make()->POST->url($this->url . "LookupBills")->header([
                    'api_key: ' . $this->apiKey,
                    "Content-Type: application/json"
                ])->body([
                    "SkuCode" => $transaction['sku_code'],
                    "AccountNumber" => $number,
                ])->execute();
            }

        };
    }
}
