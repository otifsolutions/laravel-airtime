<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class ReloadlyGiftCardProduct extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'logo_urls' => 'array',
        'fixed_recipient_denominations' => 'array',
        'fixed_sender_denominations' => 'array',
        'fixed_denominations_map' => 'array',
        'brand' => 'array',
        'country' => 'array',
        'redeem_instruction' => 'array'
    ];
    public $appends = ['amounts'];

    public function country(){
        return $this->belongsTo(ReloadlyCountry::class,'country_id');
    }
    public function getAmountsAttribute(){
        $discount = 0; // Can be set based on your logic like $discount = $this->pivot->discount
        $amounts = [];

        if($this['denomination_type'] == 'FIXED') {
            foreach ($this['fixed_denominations_map'] as $key => $denomonation)
            {
                $amounts[$key] = $denomonation + $this['sender_fee'];
                $amounts[$key] *= (1 - ($discount / 100));
                $amounts[$key] = round($amounts[$key],2);
            }
        }

        return $amounts;
    }

    public function transactions(){
        return $this->hasMany(ReloadlyGiftCardTransaction::class,'product_id');
    }
}
