<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DingConnectOperator extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'region_code' => 'json',
        'payment_type' => 'json'
    ];

    public function country() {
        return $this->belongsTo(DingConnectCountry::class);
    }

    public function products() {
        return $this->hasMany(DingConnectProduct::class);
    }

}
