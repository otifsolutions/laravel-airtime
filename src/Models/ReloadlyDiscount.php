<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReloadlyDiscount extends Model {
    use SoftDeletes;
    
    protected $guarded = [];
}
