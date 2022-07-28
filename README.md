
### Laravel Airtime
___________________
The package will use these four listed serivce, hit their API's and get all the data synced in
mySQL database tables got from the response by using the artisan commands.

**Requirements**

`laravel >= 8.0`

`php => 7.4`


### Installation
[Composer](https://getcomposer.org/download/) required to install the package

```
 composer require otifsolutions/laravel-airtime
```

then run the migrations

```
 php artisan migrate
```

## Table of Contents
1. [Reloadly](#reloadly)
2. [Value Topup](#value-topup)
3. [Ding Connect](#ding-connect)
4. [D Tone](#d-tone)


### Reloadly:

It is the service that deals with topups transactions among users from 800+ of operators around the globe,
maintains detailed record of all the successfull/unsuccessful transactions happened between operators and users


### Usage

Sign-up at [Reloadly](https://www.reloadly.com/) and get the keys from [Reloadly/keys](https://www.reloadly.com/developers/api-settings),
the keys will be used with the package, you have to grab these keys and give them to the package by `tinker`.
The third one is mode, wheter it'll be `LIVE` or `TEST`. We are using [Setting](https://github.com/otifsolutions/laravel-settings)
package to set the keys

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_key', 'API Client ID')
```

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_secret', 'API Client Secret')
```

```php
 \OTIFSolutions\Laravel\Settings\Models\Setting::set('reloadly_api_mode', 'MODE')
```


To send the transactions, create the object of heler class `Reloadly` and it's static method takes three parameters,
`key`, `secret` and `mode`. Simply you can get the settings here with `Setting::get('key')`. `Reloadly::Make` will
return an object, that object has `sendTopup` method that takes object of `\OTIFSolutions\LaravelAirtime\Models\ReloadlyTransaction`.

```php
$obj = Reloadly::Make(key, secret, mode);
$obj->sendTopup($reloadlyTransactionObj);
```



#### Command

After installing package, you'll have artisan command

```
 php artisan sync:reloadly
```

It will synchronise all of the data came from the response, you can run it or shedule it. To shedule it,
go to your project `App\Console\Kernel` class and in

```php
protected function schedule(Schedule $schedule) {
    $schedule->command('sync:reloadly')->weekly();
    // $schedule->command('sync:reloadly')->monthly();
    // $schedule->command('sync:reloadly')->daily();
}
```

You can even schedule it to run on a specific date and time. More more info, visit
[Scheduling Artisan Commands](https://laravel.com/docs/master/scheduling#scheduling-artisan-commands)



### Model Relationships


**Model relationships for Reloadly Service**

| Model           | Relation   |Model               |
| --------------- |:----------:|:------------------:|
| ReloadlyCountry | 1-m        | ReloadlyOperator   |
| Currency        | 1-1        | ReloadlyOperator   |
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |
| ReloadlyOperator| 1-m        | ReloadlyTransaction|



## Value Topup
______________
The service is same as above, just go to the site, register, grab the `username` and `password`,
give to the package via command given. Hit the command to populate the data coming from the `API`
response.


#### Commands
After the migrations run successfully, you have to give the `username` & `password` via command. Use *tinker*
to run the commands

**For username**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_user_id', 'userid');
```

**For password**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_password', 'password');
```

**For mode test/live**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('value_topup_api_mode', 'MODE');
```

**Artisan command to sync the data**
```php
php artisan sync:valuetopup
```

**Artisan command to check the status**
```php
php artisan sync:valuetopupstatus
```

### Model Relationships

**Model relationships for Value Topup Service**

| Model                | Relation   |Model                     |
| :------------------: |:----------:|:------------------------:|
| ValueTopupCategory   | 1-m        | ValueTopupCountry        |
| ValueTopupCategory   | 1-m        | ValueTopupOperator       |
| ValueTopupOperator   | 1-m        | ValueTopupProducts       |
| ValueTopupCategory   | 1-m        | ValueTopupTransaction    |
| ValueTopupCategory   | 1-m        | ValueTopupTransaction    |
| ValueTopupCountry    | 1-m        | ValueTopupTransaction    |
| ValueTopupOperator   | 1-m        | ValueTopupTransaction    |


## Ding Connect
_______________

### Usage



#### Commands

**For from ding connect**
```php
\OTIFSolutions\Laravel\Settings\Models\Setting::set('ding_connect_token', 'token');
```




### Model Relationships




## D Tone
---------

### Usage



#### Commands






### Model Relationships






#### Licence
____________
The MIT License (MIT). Please see [**License file**](https://github.com/otifsolutions/laravel-airtime/blob/main/LICENSE) for more information

