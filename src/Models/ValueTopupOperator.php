<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupOperator extends Model {

    protected $guarded = ['id'];

    public function country() {
        return $this->belongsTo(ValueTopupCountry::class);
    }

    public function products() {
        return $this->hasMany(ValueTopupProduct::class);
    }

}
