
### Laravel Airtime
___________________
The package will use these four listed serivces, hit those API's and get all the data synced in
mySQL database tables got from the response by using the artisan commands.

**Requirements**

`laravel >= 8.0`

`php >= 7.4`


### Via Composer installation:
:link: [Composer](https://getcomposer.org/download/) required to install the package

```
 composer require otifsolutions/laravel-airtime
```

We have used the :link: [Setting](https://github.com/otifsolutions/laravel-settings) package by :link: [OTIF Solutions](https://github.com/otifsolutions) to set
the keys we use
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('key', 'value', 'type');
```

To get that specific value against that `key` :key:
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::get('key');
```

The package uses these four listed services, consider if that particular service is `enabled` or `disabled`.
Or, you can make it enable by setting `true`. Don't forget to add `bool`.


**Note :books:**
By default, all these services are `disabled`. You have to enable service of your choice via `Setting::set`. Please,
don't forget to add third argument as `bool`, it defines the type of the key.

Do not set the keys directly in your code but it is wise to use `php artisan tinker` and then run
the commands for service of your choice


**AND**

To check if service is enabled or disabled, do `Setting::get('service_name_service')`


**MySQL table storage engine**

Keep in mind that putting and pulling out data to/from mysql tables is different using different
mySQL storage engines. By default the current engine is *InnoDB*. If you don't know the difference between
these storage engines, visit :link: [MyIsam & InnoDB](https://phoenixnap.com/kb/myisam-vs-innodb).To switch the
engine to *MyISAM* do this :point_down: and make the key `myisam_engine` `true`

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('myisam_engine', true, 'bool');
```


*After all, run the migrations using this command :point_down:*


```
 php artisan migrate
```
Which will run the package's migrations, the migration for table `airtime_currencies` will only be run
while the other migrations are holded there waiting for running, they'll only be run when that parcular 
service will be enabled *made true through Setting package*.


**Artisan comamnds in this package:**

To check which commands are available for whole of this `airtime` package, simple hit this command, and look against the key `sync:xxxxxxxx_xxxxxx_xxxxxxx`

```
php artisan
```

If you hit the command for syncing data for a specific service without activating/enabling it,
the package will not allow you to do that. It'll ask you to enable it firlst.



## Table of Contents

**1.** :link: [Reloadly](#reloadly)

**2.** :link: [Value Topup](#value-topup)

**3.** :link: [Ding Connect](#ding-connect)

**4.** :link: [D Tone](#d-tone)


## Reloadly 

It is the service that deals with topups transactions among users from 800+ of operators around the globe,
maintains detailed record of all the successfull/unsuccessful transactions happened between operators and users

:heavy_check_mark: **For enabling Reloadly service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_service', true, 'bool');
```



### Usage :

Sign-up at :link: [Reloadly](https://www.reloadly.com/) and get the keys from :link: [Reloadly/keys](https://www.reloadly.com/developers/api-settings),
the keys will be used with the package, you have to grab these keys and give them to the package by `tinker`.
The third one is mode, wheter it'll be `LIVE` or `TEST`. We are using :link: [Setting](https://github.com/otifsolutions/laravel-settings)
package to set the keys


To send the transactions, create the object of heler class `Reloadly` and it's static method takes three parameters,
`key`, `secret` and `mode`. Simply you can get the settings here with `Setting::get('key')`. `Reloadly::Make` will
return an object, that object has `sendTopup` method that takes object of `\OTIFSolutions\LaravelAirtime\Models\ReloadlyTransaction`.


```php
$obj = Reloadly::Make(key, secret, mode);
$obj->sendTopup($reloadlyTransactionObj);
```



#### Commands for setting relaodly credentials :

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

:heavy_check_mark: **To schedule command**

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


### Sending transactions  :



### How artisan command <u>sync:reloadly</u> works:



### Model Relationships :


| Model           | Relation   |Model               | Foreign Key                 |
| --------------- |:----------:|:------------------:|:---------------------------:|
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |                             |
| ReloadlyOperator| 1-m        | ReloadlyTransaction|                             |  
| ReloadlyOperator| 1-m        | ReloadlyDiscount   |                             |
| ReloadlyCountry | 1-m        | ReloadlyOperator   |:key: country_id             |
| AirtimeCurrency | 1-1        | ReloadlyOperator   |:key: sender_currency_id     |
| AirtimeCurrency | 1-1        | ReloadlyOperator   |:key: destination_currency_id|



## Value Topup

The service is same as above, just go to the site, register, grab the `user_id` and `password`,
give to the package via command given. Hit the command to synchronize the data coming from the `API`
response.

:heavy_check_mark: **For enabling Value-topup service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_service', true, 'bool');
```




#### Commands :
After the migrations run successfully, you have to give the `user_id` & `password` via command. Use *tinker*
to run the commands

:heavy_check_mark: **For user_id**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_user_id', 'userid');
```

:heavy_check_mark: **For password**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_password', 'password');
```

:heavy_check_mark: **For mode test/live**
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

### Send transaction using value-topup:


### How artisan command <u>sync:value_topup</u> works:



### Model Relationships :


| Model                | Relation   |Model                     | Foreign Key       |
| :------------------: |:----------:|:------------------------:|:-----------------:|
| ValueTopupCategory   | 1-m        | ValueTopupCountry        |                   |
| ValueTopupCategory   | 1-m        | ValueTopupOperator       |                   |
| ValueTopupOperator   | 1-m        | ValueTopupProducts       |                   |
| ValueTopupCategory   | 1-m        | ValueTopupTransaction    |:key: category_id  |
| ValueTopupCountry    | 1-m        | ValueTopupTransaction    |:key: country_id   |
| ValueTopupOperator   | 1-m        | ValueTopupTransaction    |:key: operator_id  |
| ValueTopupProduct    | 1-m        | ValueTopupTransaction    |:key: product_id   |


## Ding Connect

The procedure behind this service is also the same, sending *balance/topup* from *operator* to *user*
and there is made a transaction is made database. You only have to give *token/key* to make it functional.


### Usage :



:heavy_check_mark: **For enabling Ding-connect service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('ding_connect_service', true, 'bool');
```



#### Commands :

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

### Sending transaction :


### How artisan command <u>sync:ding_connect</u> works :


### Model Relationships :


| Model                | Relation   |Model                   | Foreign Key                      |
| :-------------------:|:----------:|:----------------------:|:--------------------------------:|
| DingConnectCountry   | 1-m        | DingConenctOperator    |                                  |
| DingConnectCountry   | 1-m        | DingConnectProduct     |                                  |
| DingConnectOperator  | 1-m        | DingConnectProduct     |                                  |
| DingConenctProduct   | 1-m        | DingConnectTransaction |                                  |
| DingConnectOperator  | 1-m        | DingConnectTransaction |                                  |
| AirtimeCurrency      | 1-m        | DingConnectProduct     | :key: currency_id                |
| AirtimeCurrency      | 1-m        | DingConenctProduct     | :key: destination_currency_id    |




## D Tone

The service has the same concept behind, it is used to send `topup/balance` to users.


### Usage :


To use this package, we'll set the `dtone_currency` via `Setting::set()`, it'll have currency name such as *EUR*, *USD* or *PKR*. So, set it right here before running other D-Tone commands :point_down:

```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_currency', 'currency_name', 'string');
```

:heavy_check_mark: **For enabling D-Tone service**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('dtone_service', true, 'bool');
```



#### Commands

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

### How artisan command <u>sync:dtone</u> works:


### Sending transaction :




### Model Relationships


| Model          | Relation   |Model             | Foreign Key                  |
| :-------------:|:----------:|:----------------:|:----------------------------:|
| DToneCountry   | 1-m        | DToneOperator    |                              |
| DToneCountry   | 1-m        | DToneProduct     |                              |
| DToneOperator  | 1-m        | DToneTransaction |                              |
| DToneProduct   | 1-m        | DToneTransaction |                              |
| AirtimeCurrency| 1-m        | DToneProduct     |:key: sender_currency_id      |
| AirtimeCurrency| 1-m        | DToneProduct     |:key: destination_currency_id |



#### Licence

The MIT License (MIT). Please see  :link: [**License file**](https://github.com/otifsolutions/laravel-airtime/blob/main/LICENSE) for more information

