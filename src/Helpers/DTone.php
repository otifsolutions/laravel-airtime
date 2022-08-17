<?php

namespace OTIFSolutions\LaravelAirtime\Helpers;

use OTIFSolutions\CurlHandler\Curl;
use OTIFSolutions\LaravelAirtime\Models\{DToneCountry, DToneOperator, DToneTransaction};

class DTone {

    public static function TShop($username = null, $token = null) {
        return new class($username, $token) {

            protected $username;
            protected $token;
            private $url = "https://airtime-api.dtone.com/cgi-bin/shop/topup";

            private function makeRequest($body) {
                $rand = random_int(0, time() / 2);
                $time = time() - $rand;
                $xml = "<xml>" .
                    "<login>" . $this->username . "</login>" .
                    "<key>" . $time . "</key>" .
                    "<md5>" . md5($this->username . $this->token . $time) . "</md5>" .
                    $body .
                    "</xml>";
                $response = Curl::Make()->POST->url($this->url)->header([
                    'Content-Type: text/xml;'
                ])->body($xml)->execute();
                if ($response['TransferTo']['error_code'] === "925") {
                    return $this->makeRequest($body);
                }
                return $response;
            }

            public function __construct($username, $token) {
                $this->username = $username;
                $this->token = $token;
            }

            public function setCredentials($username, $token): object {
                $this->username = $username;
                $this->token = $token;

                return $this;
            }

            public function getBalance(): ?string {
                $response = $this->makeRequest("<action>check_wallet</action>");
                return $response["TransferTo"]["balance"] ?? null;
            }

            public function getAccountInformation($number) {
                $response = $this->makeRequest("<action>msisdn_info</action><destination_msisdn>" . $number . "</destination_msisdn>");
                if (isset($response['TransferTo']['operatorid'])) {
                    return DToneOperator::where('t_shop_id', $response['TransferTo']['operatorid'])->first();
                }
                return null;
            }

            public function getOperators(DToneCountry $country) {
                $response = $this->makeRequest("<info_type>country</info_type><content>" . $country['t_shop_id'] . "</content><action>pricelist</action>");
                return $response['TransferTo'] ?? null;
            }

            public function getProducts($operatorId) {
                $response = $this->makeRequest("<info_type>operator</info_type><content>" . $operatorId . "</content><action>pricelist</action>");
                return $response['TransferTo'] ?? null;
            }

            public function sendTransfer(DToneTransaction $transaction): bool {
                $number = $transaction['number'];
                if (strpos($number, "0") === 0) {
                    $number = substr($number, 1);
                }
                $code = $transaction['operator']['country']['dial_code'];
                if (!(strpos($number, $code) === 0 || strpos($number, "+" . $code) === 0)) {
                    $number = $code . $number;
                }
                if (str_contains(strtoupper($transaction['operator']['name']), 'PIN'))
                    $transaction['response'] = $this->makeRequest("<msisdn>" . $transaction['sender_phone_no'] . "</msisdn><destination_msisdn>" . $number . "</destination_msisdn><operatorid>" . $transaction['operator']['t_shop_id'] . "</operatorid> <product>" . $transaction['product'] . "</product><action>topup</action>");
                else
                    $transaction['response'] = $this->makeRequest("<msisdn>" . $transaction['sender_phone_no'] . "</msisdn><destination_msisdn>" . $number . "</destination_msisdn><product>" . $transaction['product'] . "</product><action>topup</action>");
                if ($transaction['response']["TransferTo"]["error_code"] === "0") {
                    $transaction['status'] = 'SUCCESS';
                    $transaction->save();
                    return true;
                }
                $transaction['status'] = 'FAIL';
                $transaction->save();
                return false;
            }

        };
    }

}
