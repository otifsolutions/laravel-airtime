<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyOperator extends Model {
    use SoftDeletes;

    protected $guarded = [];

    public function country() {
        return $this->belongsTo(ReloadlyCountry::class);
    }

    public function discount() {
        return $this->hasOne(ReloadlyDiscount::class);
    }

    public function promotions() {
        return $this->hasMany(ReloadlyPromotion::class);
    }

    public function transactions() {
        return $this->hasMany(ReloadlyTransaction::class);
    }

    public function currency() {
        return $this->hasOne(Currency::class);
    }

}
