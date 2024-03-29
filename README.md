
### Laravel Airtime
___________________

Versatile package that uses third party services to send balance/topup to users/phoneNumbers

**Requirements**

`laravel >= 8.0`

`php >= 7.4`


<br>

### Via Composer installation:
:link: [Composer](https://getcomposer.org/download/) required to install the package

```
 composer require otifsolutions/laravel-airtime
```

We have used the :link: [Setting](https://github.com/otifsolutions/laravel-settings) package by :link:
[OTIF Solutions](https://github.com/otifsolutions) to set

The keys we use
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('key', 'value', 'type');
```

To get that specific value against that `key` :key:
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::get('key');
```

The package uses these four listed services, consider if that particular service is `enabled` or `disabled`.
Or, you can make it enable by setting `true`. Don't forget to add `bool`.

<br>

**Note :books:**

By default, all these services are `disabled`. You have to enable service of your choice via `Setting::set`. Please,
don't forget to add third argument as `bool`, it defines the type of the key.

Do not set the keys directly in your code but it is wise to use `php artisan tinker` and then run
the commands for service of your choice


**AND**

> To check if service is enabled or disabled, do `Setting::get('service_name_service')`


<br>


**MySQL table storage engine**

Keep in mind that putting and pulling out data to/from mysql tables is different using different
mySQL storage engines. By default the current engine is *InnoDB*. If you don't know the difference between
these storage engines, visit :link: [MyIsam & InnoDB](https://phoenixnap.com/kb/myisam-vs-innodb).To switch the
engine to *MyISAM* do this :point_down: and make the key `myisam_engine` `true`

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('myisam_engine', false, 'bool');
```


After all, run the migrations using this command :point_down:


```
 php artisan migrate
```

Which will run the package's migrations, the migration for table `airtime_currencies` will only be run
while the other migrations are holded there waiting for running, they'll only be run when that particular 
service will be enabled *made true through Setting package*.


**Artisan comamnds in this package:**

To check which commands are available for whole of this `airtime` package, simple hit this command, and look against the key `sync:xxxxxxxx_xxxxxx_xxxxxxx`

```
 php artisan
```

If you hit the command for syncing data for a specific service without activating/enabling it,
the package will not allow you to do that. It'll ask you to enable it first.


<br>


## Table of Contents

- [Reloadly](#reloadly)
    - [Usage](#usage)
    - [Commands for setting relaodly credentials](#commands-for-setting-relaodly-credentials)
    - [Scheduling command](#scheduling-command)
    - [How to send transactions](#how-to-send-transactions)
    - [How to send gift cards](#how-to-send-gift-cards)
    - [How to pay utility bills](#how-to-pay-utility-bills)
    - [How artisan sync comamnd works for this service](#how-artisan-sync-comamnd-works-for-this-service)
    - [Model relations for reloadly](#model-relations-for-reloadly)
- [Value Topup](#value-topup)
    - [Commands](#commands)
    - [Sending transactions](#sending-transactions)
    - [Other transaction methods](#other-transaction-methods)
        - [Topup Transaction](#topup-transaction)
        - [Pin transaction](#pin-transaction)
        - [Card transaction](#card-transaction)
        - [Bill pay transaction](#bill-pay-transaction)
    - [How sync command works behind the scene](#how-sync-command-works-behind-the-scene)
    - [Model relationships for ValueTopup](#model-relationships-for-ValueTopup)
- [Ding Connect](#ding-connect)
    - [Usage](#usage)
    - [Commands](#commands)
    - [Sending transaction](#sending-transaction)
    - [How sync comamnd works in action](#how-sync-comamnd-works-in-action)
    - [Model relationships for Ding Connect](#model-relationships-for-ding-connect)
- [D Tone](#d-tone)
    - [How to use this service](#how-to-use-this-service)
    - [Settings and commands](#settings-and-commands)
    - [Sending transaction](#sending-transaction)
    - [How sync command works](#how-sync-command-works)
    - [Model Relationships for D Tone Service](#model-relationships-for-d-tone-service)


<br><br>


## Reloadly 

It is the service that deals with topups transactions among users from 800+ of operators around the globe,
maintains detailed record of all the successfull/unsuccessful transactions happened between operators and users

:heavy_check_mark: **For enabling Reloadly service**

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_service', true, 'bool');
```



### Usage

Sign-up at :link: [Reloadly](https://www.reloadly.com/) and get the keys from :link: [Reloadly/keys](https://www.reloadly.com/developers/api-settings),
the keys will be used with the package, you have to grab these keys and give them to the package by `tinker`.
The third one is mode, wheter it'll be `LIVE` or `TEST`. We are using :link: [Setting](https://github.com/otifsolutions/laravel-settings)
package to set the keys



#### Commands for setting relaodly credentials

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_key', 'API Client ID');
```

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_secret', 'API Client Secret');
```

If working with sandbox, mode will be `TEST`, otherwise it'll be `LIVE` 

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_mode', 'MODE');
```

After installing package, you'll have artisan command, hit this :point_down:

```
 php artisan sync:reloadly
```


### Scheduling command

It will synchronise all of the data came from the response. To shedule it,
go to your project `App\Console\Kernel` class and in

```php
protected function schedule(Schedule $schedule) {
    $schedule->command('sync:reloadly')->weekly();
    // $schedule->command('sync:reloadly')->monthly();
    // $schedule->command('sync:reloadly')->daily();
}
```

You can even schedule this command to run on a specific date and time. For more info, visit
:link: [Scheduling Artisan Commands](https://laravel.com/docs/master/scheduling#scheduling-artisan-commands)


### How to send transactions
To send transaction, create an object of `ReloadlyTransaction` with properties, pass as parameter to the `Reloadly` helper
class method `sendTopup(ReloadlyTransaction $reloadlyTransactionObj)` and execute it

```php

$rdHelperObj = Reloadly::Make($key, $secred, $mode);

$rdTransaction = ReloadlyTransaction::create([
        'operator_id' => 1, // the operator id, under which operator the transaction is being from total 800+ operators
        'is_local' => false,   // Indicates either transaction is in operator currency in which customer will get airtime or its in currency of Reloadly account being used
        'topup' => 100,     // amount in receiving currency
        'amount' => 125,    //  amount in sending currency
        'number' => '00923219988771',     // the connected phone number to which transaction has to be made
        'sender_currency' => 'PKR',     // currency from which transaction is being made
        'destination_currency' => 'PKR'     // currency of transaction receiving channel/user
    ]);

$rdHelperObj->sendTopup($rdTransaction);
```
Other fields that are to be filled with some `values/jsons` on API response for each transaction object

| Colomn      | Detail                                                                                 |
|:----------- |:-------------------------------------------------------------------------------------- |
| status      | Status of current transaction whether it is SUCCESS or FAIL                            |
| response    | When API request is hit, some kind of json details                                     |
| pin         | In case of purchasing pin, this is pin number, filled when request is hit              |


<br>



### How to send gift cards
To send transaction, create an object of `ReloadlyGiftCardTransaction` with properties, call the sendTransaction() method on that object. 

```php

$rdHelperObj = Reloadly::Make($key, $secred, $mode);
$rdHelperObj->getGiftToken();
$giftCard = ReloadlyGiftCardProduct::find($request['gift_id']); // selected gift card
$rdTransaction = ReloadlyGiftCardTransaction::create([
        'user_id' => $user['id'], // user who is using the service
        'email' => $request['email'], // customr/user for whick service is used
        'invoice_id' => $invoice['id'], // invoice for the record 
        'product_id' => $giftCard['id'],
        'product' => $giftCard, // the reloadly gift card 
        'recipient_currency_id' => $recipientCurrency['id'], // currency from which transaction is being made
        'sender_currency_id' => $senderCurrency['id'], // currency of transaction receiving channel/user
        'sender_amount' => $giftCard['fixed_sender_denominations'][$paymentIndex] + $giftCard['sender_fee'], // You can add your fee as well 
        'reloadly_fee' => $giftCard['sender_fee'], // The fee of the gift card
        'recipient_amount' => $giftCard['fixed_recipient_denominations'][$request['selected_index']], // The price of the gift card
        'reference' => Str::random(10),
    ]);
$rdTransaction->sendTransaction();

```


### How to pay utility bills
To send transaction, create an object of `ReloadlyUtilityTransaction` with properties, pass as parameter to the `Reloadly` helper
class method `sendTopup(ReloadlyTransaction $reloadlyTransactionObj)` and execute it

```php

$rdHelperObj = Reloadly::Make($key, $secred, $mode);
$rdHelperObj->getUtilityToken();
$utilityBiller = ReloadlyUtility::find($request['biller_id']); // The selected biller for which bill is paying.

$rdTransaction = ReloadlyUtilityTransaction::create([
        'user_id' => $user['id'], // user who is using the service
        'utility_id' => $utilityBiller['id'], // user who is using the service
        'subscriber_account_number' => $request['account_number'], // customr/user no. for which service is used 
        'is_local' => $request['is_local'], // Local amount or international amount 
        'amount' => 10000, // The amount paid to the biller
        'reference_id' => Str::random(10),
    ]);
$rdHelperObj->payUtilityBill($rdTransaction);

```
Now You need to confirm the transaction by add this line:
```
$rdHelperObj->confirmReloadlyUtilityTransaction($rdTransaction);
``` 
OR by running this command 
```
php artisan sync:reloadly_utility_transaction
``` 
You Can set the command to run after every minute in **app/Console/Kernel.php**
```
$schedule->command('sync:reloadly_utility_transaction')->everyMinute();
```

### How artisan sync comamnd works for this service
- First checks if this service is enabled or not
- Migrations are run then credentials are checked
- Token is generated with credentials, got the balance and set the balance
- Soft deleting the countries to sync with active ones
- Fetching operators and syncing them with mysql tables
- Syncing promotions
- Then sync discounts

### Model relations for reloadly


| Parent Model    | Relation   | Child Model        | Foreign Key                 |
|:--------------- |:---------- |:------------------ |:--------------------------- |
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |:key: operator_id            |
| ReloadlyOperator| 1-m        | ReloadlyTransaction|:key: operator_id            |  
| ReloadlyOperator| 1-m        | ReloadlyDiscount   |:key: operator_id            |
| ReloadlyCountry | 1-m        | ReloadlyOperator   |:key: country_id             |
| ReloadlyCountry | 1-m        | ReloadlyGiftCardProduct   |:key: country_id             |
| AirtimeCurrency | 1-1        | ReloadlyOperator   |:key: sender_currency_id     |
| AirtimeCurrency | 1-1        | ReloadlyOperator   |:key: destination_currency_id|



<br><br><br>


## Value Topup

The service is same as above, just go to the site, register, grab the `user_id` and `password`,
give to the package via command given. Hit the command to synchronize the data coming from the `API`
response.

:heavy_check_mark: **For enabling Value-topup service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_service', true, 'bool');
```


#### Commands

Just simpley give the `user_id` & `password` via command. Use *tinker*

:heavy_check_mark: **For user_id**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_user_id', 'userid');
```

:heavy_check_mark: **For password**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_password', 'password');
```

:heavy_check_mark: **For mode test/live** (be careful with the mode Test/Live)
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_api_mode', 'MODE');
```

:heavy_check_mark: **Artisan command to sync the data**
```
php artisan sync:value_topup
```

:heavy_check_mark: **Artisan command to check the status**
```
php artisan sync:value_topup_status
```

<br>

### Sending transactions

This service has four methods of sending transactions named as `topupTransaction`, `pinTransaction`,
`cardTransaction` and `billPayTransaction`. Although, structure of sending transaction is the same
as there is one table `value_topup_transactions` for all transactions. Here is the overall
view of sending transaction :point_down:

```php

$vtObj = ValueTopup::Make()->setCredentials($userId, $password, $mode = 'LIVE');    // will return on object containing all the methods & becareful with the mode

$vtTransactionObj = ValueTopupTransaction::create([
        'category_id' => 2, // foreign key for category to indicate which type of transaction is created here like Airtime, Pin etc
        'country_id' => 9, // country id, like 9 for Pakistan, 13 for Panama
        'operator_id' => 16,     // operator id, under which operator the transaction is being made
        'product_id' => 1031, // id of particular product/package ranging more than 100, like 1 for product '8ta South Africa 5.40 USD'
        'reference' => '166064462062fb6d0c4ab37', // user generated truly unique string of 50 characters, Correlation ID which you send in actual topup api method
        'topup' => 100,     // it is by default in Customer Wallet Currency
        'amount' => 200,    // amount in sending currency
        'number' => '00923229988770',   // number to which we are sending transaction, destination phone number
        'sender_currency' => 'PKR', // currency type of sender user
        'receiver_currency' => 'PKR',   // receiving currency, destination currency
    ]);

    // you can generate reference by uniqid(time())

```


Other fields that are to be filled with some `values/jsons` on API response for each transaction object

| Colomn      | Detail                                                                                 |
|:----------- |:-------------------------------------------------------------------------------------- |
| status      | Status of current transaction whether it is SUCCESS or FAIL                            |
| response    | Json response by API after hitting one of given transaction method                      |


<br>


### Other transaction methods

Now here :point_down: is the detail of the provided four tansaction methods

#### Topup transaction

```php
topupTransaction($transaction)
```

> This method takes few fields filled `ValueTopupTransaction` object like `$vtTransactionObj[product][sku_id]`,
    `amount`, `number`, `reference`, `number`, `receiver_currency` and `sender_currency`,
    it hits `/transaction/topup` uri in behind

```php
$vtObj->topupTransaction($vtTransactionObj);        
```

#### Pin transaction

```php
pinTransaction($transaction)
```

> This method takes `$transactionObj` with `$obj[product][sku_id]`, `reference` and it hits `/transaction/pin` in behind

#### Card transaction

```php
cardTransaction($transaction, $firstName, $lastName, $email)
```

> This transaction method takes firstname, lastname and email in addition, in `$transactionObj` it
    takes `$obj[product][sku_id]`, `amount`, `reference` and it hits `/transaction/giftcard/order` in behind
    the scene

#### Bill pay transaction

```php
billPayTransaction($transaction)
```

> This method takes `$transactionObj` with `$obj[product][sku_id]`, `amount`, `number`,
    `reference` and `sender_currency`, it uses the endpoint `/transaction/billpay/` in the backend





### How sync command works behind the scene

- Check if service is enabled, then run its migrations one by one
- Then credentials are checked and token is generated
- Syncing operators
- Syncing products
- Syncing product descriptions
- Then syncing countries data from json file


### Model relationships for ValueTopup


| Parent Model         | Relation   | Child Model              | Foreign Key       |
| :------------------  |:---------- |:-----------------------  |:----------------  |
| ValueTopupCategory   | 1-m        | ValueTopupCountry        |:key: category_id  |
| ValueTopupCategory   | 1-m        | ValueTopupOperator       |:key: category_id  |
| ValueTopupOperator   | 1-m        | ValueTopupProducts       |:key: operator_id  |
| ValueTopupCategory   | 1-m        | ValueTopupTransaction    |:key: category_id  |
| ValueTopupCountry    | 1-m        | ValueTopupTransaction    |:key: country_id   |
| ValueTopupOperator   | 1-m        | ValueTopupTransaction    |:key: operator_id  |
| ValueTopupProduct    | 1-m        | ValueTopupTransaction    |:key: product_id   |


<br><br><br>




## Ding Connect

The procedure behind this service is also the same, sending *balance/topup* from *operator* to *user*
and there is made a transaction is made database. You only have to give *token/key* to make it functional.


### Usage



:heavy_check_mark: **For enabling Ding-connect service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('ding_connect_service', true, 'bool');
```



#### Commands

**To give token** :point_down:

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('ding_connect_token', 'token', 'string');
```

after *setting/giving* the token, you can successfully execute the below given `artisan comamnd`,
it will synchronize the *countries*, *operators* and *products*. The process will work in background, just
execute the command and leave the tab open.

```
 php artisan sync:ding_connect
```

### Sending transaction

To send transaction using *Ding Connect Service*, first create obj of helper class by giving 
the `API_Key` or `Token` to `DingConenct::Make()` method, it'll return an object having 
`sendTranser()` method like this :point_down:

```php
$dingConenctObj = DingConnect::Make($tokenOrKey);

$dcTransactionObj = DingConenctTransaction::create([
        'operator_id' => 1,     // id from any total 600+ operators under which transaction is made, like 1 for operator name 'Digicel Guyana'
        'product_id' => 1,  // the product/package id from one of 3300+ products which is being baught
        'sku_code' => 'GY_DC_TopUp', // unique sku code provided by API to indicate which product is being bought.
        'send_value' => 200,    // the amount to be sent
        'send_currency_code' => 'PKR',      // the currency of sender side
        'number' => '00923219988771',  // sender phone number sample
        'ref' => 'reference',     // distributor reference
    ]);

$dingConenctObj->sendTransfer($dcTransactionObj);
```

<br>

Other fields that are to be filled with some `values/jsons` on API response for each transaction object

| Colomn      | Detail                                                                                 |
|:----------- |:-------------------------------------------------------------------------------------- |
| status      | Status of current transaction whether it is SUCCESS or FAIL                            |
| response    | JSON response when send transaction request is hit                                     |


<br>





### How sync comamnd works in action
- Check if this servie is enabled
- Run its migrations
- Check the credentials, show user-friendly error message if wrong
- Sync countries data from json file
- Sync countries `name`, `iso2` and `dial_code`
- Syncing operators
- Syncing products

### Model relationships for Ding Connect


| Parent Model         | Relation   | Child Model            | Foreign Key                      |
| :------------------- |:---------- |:---------------------  |:-------------------------------  |
| DingConnectCountry   | 1-m        | DingConenctOperator    | :key: country_id                 |
| DingConnectCountry   | 1-m        | DingConnectProduct     | :key: country_id                 |
| DingConnectOperator  | 1-m        | DingConnectProduct     | :key: operator_id                |
| AirtimeCurrency      | 1-m        | DingConnectProduct     | :key: currency_id                |
| AirtimeCurrency      | 1-m        | DingConenctProduct     | :key: destination_currency_id    |
| DingConenctProduct   | 1-m        | DingConnectTransaction | :key: product_id                 |
| DingConnectOperator  | 1-m        | DingConnectTransaction | :key: operator_id                |

<br><br><br>


## D Tone

The service has the same concept behind, it is used to send `topup/balance` to users.


### How to use this service


To use this service, set the `dtone_currency` via `Setting::set()`, it'll have currency name such as *EUR*, *USD* or *PKR*. So, set it right here before running other D-Tone commands :point_down:

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_currency', 'currency_name', 'string');
```

:heavy_check_mark: **For enabling D-Tone service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_service', true, 'bool');
```

<br>

#### Settings and commands

First set :point_down:
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_tshop_username', 'value-username', 'string');
```

And :point_down:
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_tshop_token', 'value-token', 'string');
```



To sync data with using `D-Tone` platform, hit this command :point_down:
```
php artisan sync:dtone
```

<br>

### Sending transaction

To send the transaction using this service, create an object of `DToneTransaction`, pass as parameter
to helper class `DTone` method `sendTransfer()`, for understand this code snippet :point_down:

```php

$dToneObj = DTone::Make($username, $token);

$dtoneTransactionObj = DToneTransaction::create([
        'operator_id' => 1, // id from any operators available for this service
        'product_id' => 1,  // id of any product/package which is being baught by the user
        'sender_phone_no' => '00923219988771',     // the phone number which is about to send the transaction
        'number' => '00923217878776',    // transaction receiver phone number, destination phone number
        'product' => 'some-type', // the type of package/product user has baught
    ]);

$dToneObj->sendTransfer($dtoneTransactionObj);
```


<br>

Other fields that are to be filled with some `values/jsons` on API response for each transaction object

| Colomn      | Detail                                                                                 |
|:----------- |:-------------------------------------------------------------------------------------- |
| status      | Status of current transaction whether it is SUCCESS or FAIL                            |
| response    | JSON response when send transaction request is hit                                     |


<br>



### How sync command works
- Check D-Tone currency and service
- Run the migrations and check the credentials
- Sync countries in `name`, `dial_code` and `t_shop_id`
- Sync operators
- Sync products




### Model Relationships for D Tone Service


| Parent Model   | Relation   | Child Model      | Foreign Key                  |
| :------------  |:---------  |:---------------  |:---------------------------  |
| DToneCountry   | 1-m        | DToneOperator    |:key: country_id              |
| DToneOperator  | 1-m        | DToneTransaction |:key: operator_id             |
| DToneProduct   | 1-m        | DToneTransaction |:key: product_id              |
| DToneCountry   | 1-m        | DToneProduct     |:key: country_id              |
| AirtimeCurrency| 1-m        | DToneProduct     |:key: sender_currency_id      |
| AirtimeCurrency| 1-m        | DToneProduct     |:key: destination_currency_id |



#### Licence

The MIT License (MIT). Please see  :link: [**License file**](https://github.com/otifsolutions/laravel-airtime/blob/main/LICENSE) for more information

