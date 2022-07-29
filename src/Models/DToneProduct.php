<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DToneProduct extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    public function country() {
        return $this->belongsTo(DToneCountry::class);
    }

    public function operator() {
        return $this->belongsTo(DToneOperator::class);
    }

    public function senderCurrency() {
        return $this->belongsTo(Currency::class);
    }

    public function destinationCurrency() {
        return $this->belongsTo(Currency::class);
    }

}
