<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupTransaction extends Model {

    protected $guarded = ['id'];

    protected $casts = [
        'response' => 'json'
    ];

    public function category() {
        return $this->belongsTo(ValueTopupCategory::class);
    }

    public function country() {
        return $this->belongsTo(ValueTopupCountry::class);
    }

    public function operator() {
        return $this->belongsTo(ValueTopupOperator::class);
    }

    public function product() {
        return $this->belongsTo(ValueTopupProduct::class);
    }

}
