<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DingConnectOperator extends Model {

    use SoftDeletes;

    protected $casts = [
        'region_code' => 'json',
        'payment_type' => 'json'
    ];

    protected $guarded = ['id'];

}
