<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DToneCountry extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    public function operators() {
        return $this->hasMany(DToneOperator::class);
    }

}
