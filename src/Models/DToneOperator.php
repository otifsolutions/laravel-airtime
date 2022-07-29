<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DToneOperator extends Model {

    use SoftDeletes;

    protected $guarded = ['id'];

    public function country() {
        return $this->belongsTo(DToneCountry::class);
    }

}
