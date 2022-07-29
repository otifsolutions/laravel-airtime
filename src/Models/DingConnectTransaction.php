<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;

class DingConnectTransaction extends Model {

    protected $guarded = ['id'];

    public function operator() {
        return $this->belongsTo(DingConnectOperator::class);
    }

    public function product() {
        return $this->belongsTo(DingConnectProduct::class);
    }

}
