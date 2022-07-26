<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupCountry extends Model {

    protected $guarded = ['id'];

    public function category() {
        return $this->belongsTo(ValueTopupCategory::class);
    }

    public function operators() {
        return $this->hasMany(ValueTopupOperator::class);
    }

}
