<?php

namespace OTIFSolutions\LaravelAirtime\Models;


use Illuminate\Database\Eloquent\Model;

class ValueTopupProduct extends Model {

    protected $guarded = ['id'];

    protected $caste = [
        'international_country_code' => 'json'
    ];




}
