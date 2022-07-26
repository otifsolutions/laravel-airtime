<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ValueTopupCategory extends Model {

    protected $guarded = ['id'];

    public function countries() {
        return $this->hasMany(ValueTopupCountry::class);
    }

}
