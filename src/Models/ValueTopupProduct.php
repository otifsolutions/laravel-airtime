<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupProduct extends Model {

    protected $guarded = ['id'];

    protected $casts = [
        'international_country_code' => 'json'
    ];

    public function operator() {
        return $this->belongsTo(ValueTopupOperator::class);
    }

}
