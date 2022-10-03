<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyUtilityTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function country(){
        return $this->belongsTo(ReloadlyCountry::class,'country_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function utility_biller(){
        return $this->belongsTo(ReloadlyUtility::class,'utility_id');
    }
}
