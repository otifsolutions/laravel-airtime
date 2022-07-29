<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class DToneTransaction extends Model {

    protected $guarded = ['id'];

    protected $casts = [
        'response' => 'json'
    ];

    public function operator() {
        return $this->belongsTo(DToneOperator::class);
    }

    public function product() {
        return $this->belongsTo(DToneProduct::class);
    }

}
