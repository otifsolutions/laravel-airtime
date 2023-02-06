<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyUtility extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function country(){
        return $this->belongsTo(ReloadlyCountry::class,'country_id');
    }

    public function transactions(){
        return $this->hasMany(ReloadlyUtilityTransaction::class,'utility_id');
    }
}
