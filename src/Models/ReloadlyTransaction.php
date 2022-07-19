<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class ReloadlyTransaction extends Model {
    protected $guarded = [];

    public function operator() {
        return $this->belongsTo(ReloadlyOperator::class);
    }

}
