<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DingConnectProduct extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'maximum' => 'json',
        'minimum' => 'json',
        'benefits' => 'json',
        'payment_types' => 'json'
    ];

}
