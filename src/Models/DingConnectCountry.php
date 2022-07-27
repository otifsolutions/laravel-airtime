<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DingConnectCountry extends Model {
    use SoftDeletes;

    protected $guarded = ['id'];

}
