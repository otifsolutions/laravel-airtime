<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupTransaction extends Model {

    protected $guarded = ['id'];

    protected $caste = [
        'response' => 'json',
        'details' => 'json'
    ];

}
