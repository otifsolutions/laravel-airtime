
### Laravel Airtime
___________________
The package will use these four listed serivce, hit their API's and get all the data synced in
mySQL database tables got from the response by using the artisan commands.



## Table of Contents
1. [Reloadly](#reloadly)
2. [Ding Connect](#ding-connect)
3. [Value Topup](#value-topup)
4. [D Tone](#d-tone)


### Reloadly:
_____________



### Usage
_________



#### Commands
_____________





### Model Relationships
________________________

**Model relationships for Reloadly Service**

| Model           | Relation   |Model               |
| --------------- |:----------:|:------------------:|
| ReloadlyCountry | 1-m        | ReloadlyOperator   |
| Currency        | 1-1        | ReloadlyOperator   |
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |
| ReloadlyOperator| 1-m        | ReloadlyPromotion  |
| ReloadlyOperator| 1-m        | ReloadlyTransaction|


_____________________________________________________________________________________________

## Ding Connect


### Usage
_________



#### Commands
_____________





### Model Relationships
________________________




__________________________________________________________________________________

## Value Topup



### Usage
_________



#### Commands
_____________





### Model Relationships
________________________


__________________________________________________________________________________

## D Tone


### Usage
_________



#### Commands
_____________





### Model Relationships
________________________




_______________________________________________________________________________________________


#### Licence
____________
The MIT License (MIT). Please see [**License file**](https://github.com/otifsolutions/laravel-airtime/blob/main/LICENSE) for more information







