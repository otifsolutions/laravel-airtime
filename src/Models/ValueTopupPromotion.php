<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupPromotion extends Model {

    protected $guarded = ['id'];

    protected $caste = [
        'promotion_min_max' => 'json',
        'product' => 'json'
    ];
}
