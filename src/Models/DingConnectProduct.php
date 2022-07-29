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

    public function operator() {
        return $this->belongsTo(DingConnectOperator::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function destinationCurrency() {
        return $this->belongsTo(Currency::class);
    }

    public function country() {
        return $this->belongsTo(DingConnectCountry::class);
    }

}
