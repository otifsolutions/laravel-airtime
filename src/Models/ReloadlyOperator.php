<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyOperator extends Model {

    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'logo_urls' => 'json',
        'fixed_amounts' => 'json',
        'fixed_amounts_descriptions' => 'json',
        'local_fixed_amounts' => 'json',
        'local_fixed_amounts_descriptions' => 'json',
        'suggested_amounts' => 'json',
        'suggested_amounts_map' => 'json'
    ];

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
