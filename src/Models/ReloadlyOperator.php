<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyOperator extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

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
        return $this->belongsTo(ReloadlyCountry::class,'country_id');
    }

    public function discount() {
        return $this->hasOne(ReloadlyDiscount::class,'operator_id');
    }

    public function promotions() {
        return $this->hasMany(ReloadlyPromotion::class,'operator_id');
    }

    public function transactions() {
        return $this->hasMany(ReloadlyTransaction::class,'operator_id');
    }

    public function currency() {
        return $this->hasOne(AirtimeCurrency::class);
    }

}
