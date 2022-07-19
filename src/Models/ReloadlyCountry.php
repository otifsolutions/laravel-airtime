<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyCountry extends Model {
    use SoftDeletes;

    protected $guarded = [];

    public function operators() {
        return $this->hasMany(ReloadlyOperator::class);
    }

}
