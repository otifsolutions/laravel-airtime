<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyCountry extends Model {
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['calling_codes' => 'json'];

    public function operators() {
        return $this->hasMany(ReloadlyOperator::class);
    }

    public  function gifts(){
        return $this->hasMany(ReloadlyGiftCardProduct::class,'country_id');
    }

}
