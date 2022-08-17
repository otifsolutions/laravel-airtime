<?php

namespace OTIFSolutions\LaravelAirtime\Helpers;

use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\Laravel\Settings\Models\Setting;

/**
 * Class ValueTopup
 * @package OTIFSolutions\LaravelAirtime
 */
class ValueTopup {

    /**
     * @return object
     */
    public static function Make(): object {
        return new class() {

            private $mode;
            private $userId;
            private $password;
            private $token;

            public function __construct() {

                $this->token = Setting::get('value_topup_token');
                $this->mode = Setting::get('value_topup_api_mode');
            }

            private function getApiUrl(): string {
                $this->mode = Setting::get('value_topup_api_mode');
                return $this->mode === 'LIVE' ? 'https://www.valuetopup.com/api/v1' : 'https://sandbox.valuetopup.com/api/v1';
            }

            /**
             * @param $userId
             * @param $password
             * @param string $mode
             * @return object
             */
            public function setCredentials($userId, $password, $mode = 'LIVE'): object {
                $this->userId = $userId;
                $this->password = $password;
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

            public function getBalance(): array {
                return Curl::Make()->GET->url($this->getApiUrl() . "/account/balance")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getValueTopupCarrier(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/carriers")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();

            }

            public function getValueTopupProducts(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/skus")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getValueTopupProductsDescription(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/sku/description")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();

            }

            public function getValueTopupOperatorLogo(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/sku/logos")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();

            }

            public function getValueTopupCurrentPromotion(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/promotion/current")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getValueTopupUpcomingPromotion(): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/promotion/upcoming")->header([
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getValueTopupStatus($reference): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/transaction/status/" . $reference)->header([
                    "Accept: application/json",
                    "Content-Type:application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getOperatorByNumber($number): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/catalog/lookup/mobile/" . $number)->header([
                    "Accept: application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function getBillDetails($skuId, $accountNo): array {

                return Curl::Make()->GET->url($this->getApiUrl() . "/transaction/billpay/fetch-account-detail?skuId=" . $skuId . "&accountNumber=" . $accountNo)->header([
                    "Accept: application/json",
                    "Content-type: application/json",
                    "Authorization: Basic " . $this->token
                ])->execute();
            }

            public function topupTransaction($transaction) {

                $body = [
                    "skuId" => $transaction['product']['sku_id'],
                    "amount" => $transaction['amount'],
                    "mobile" => $transaction['number'],
                    "correlationId" => $transaction['reference'],
                    "boostPin" => "",
                    "numberOfPlanMonths" => 0,
                    "senderMobile" => $transaction['number'],
                    "currencyCode" => $transaction['receiver_currency'],
                    "transactionCurrencyCode" => $transaction['sender_currency'],
                ];

                $response = Curl::Make()->POST->url($this->getApiUrl() . "/transaction/topup")->header([
                    "Accept: application/json",
                    "Content-type: application/json",
                    "Authorization: Basic " . $this->token
                ])->body($body)->execute();

                if (isset($response['responseCode']) && $response['responseCode'] !== NULL && $response['responseCode'] !== '') {
                    if ($response['responseCode'] === '000') {
                        $transaction['status'] = 'SUCCESSFUL';
                    } else if ($response['responseCode'] === '851' || $response['responseCode'] === '852') {
                        $transaction['status'] = 'PROCESSING';
                    } else {
                        $transaction['status'] = 'FAIL';
                    }

                    $transaction['response'] = $response;
                    $transaction->save();

                }

                return $transaction;

            }

            public function pinTransaction($transaction) {

                $body = [
                    "skuId" => $transaction['product']['sku_id'],
                    "correlationId" => $transaction['reference'],
                    "quantity" => "1",
                ];

                $response = Curl::Make()->POST->url($this->getApiUrl() . "/transaction/pin")->header([
                    "Accept: application/json",
                    "Content-type: application/json",
                    "Authorization: Basic " . $this->token
                ])->body($body)->execute();

                if (isset($response['responseCode']) && $response['responseCode'] !== NULL && $response['responseCode'] !== '') {
                    if ($response['responseCode'] === '000') {
                        $transaction['status'] = 'SUCCESSFUL';
                    } else if ($response['responseCode'] === '851' || $response['responseCode'] === '852') {
                        $transaction['status'] = 'PROCESSING';
                    } else {
                        $transaction['status'] = 'FAIL';
                    }

                    $transaction['response'] = $response;
                    $transaction->save();

                }

                return $transaction;

            }

            public function cardTransaction($transaction, $firstName, $lastName, $email) {

                $body = [
                    "skuId" => $transaction['product']['sku_id'],
                    "amount" => $transaction['amount'],
                    "correlationId" => $transaction['reference'],
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "email" => $email,
                    "recipient" => "",
                    "message" => "",
                ];

                $response = Curl::Make()->POST->url($this->getApiUrl() . "/transaction/giftcard/order")->header([
                    "Accept: application/json",
                    "Content-type: application/json",
                    "Authorization: Basic " . $this->token
                ])->body($body)->execute();

                if (isset($response['responseCode']) && $response['responseCode'] !== NULL && $response['responseCode'] !== '') {
                    if ($response['responseCode'] === '000') {
                        $transaction['status'] = 'SUCCESSFUL';
                    } else if ($response['responseCode'] === '851' || $response['responseCode'] === '852') {
                        $transaction['status'] = 'PROCESSING';
                    } else {
                        $transaction['status'] = 'FAIL';
                    }

                    $transaction['response'] = $response;
                    $transaction->save();

                }

                return $transaction;

            }

            public function billPayTransaction($transaction) {

                $body = [
                    "skuId" => $transaction['product']['sku_id'],
                    "amount" => $transaction['amount'],
                    "accountNumber" => $transaction['number'],
                    "mobileNumber" => "",
                    "checkDigits" => "",
                    "correlationId" => $transaction['reference'],
                    "senderMobile" => "",
                    "senderName" => "",
                    "currencyCode" => $transaction['sender_currency'],
                ];

                $response = Curl::Make()->POST->url($this->getApiUrl() . "/transaction/billpay")->header([
                    "Accept: application/json",
                    "Content-type: application/json",
                    "Authorization: Basic " . $this->token
                ])->body($body)->execute();

                if (isset($response['responseCode']) && $response['responseCode'] !== NULL && $response['responseCode'] !== '') {
                    if ($response['responseCode'] === '000') {
                        $transaction['status'] = 'SUCCESSFUL';
                    } else if ($response['responseCode'] === '851' || $response['responseCode'] === '852') {
                        $transaction['status'] = 'PROCESSING';
                    } else {
                        $transaction['status'] = 'FAIL';
                    }

                    $transaction['response'] = $response;
                    $transaction->save();

                }

                return $transaction;

            }

        };
    }
}
