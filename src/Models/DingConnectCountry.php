<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DingConnectCountry extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    public function operators() {
        return $this->hasMany(DingConnectOperator::class);
    }

    public function products() {
        return $this->hasMany(DingConnectProduct::class);
    }

}
