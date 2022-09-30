<?php

namespace OTIFSolutions\LaravelAirtime\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\Reloadly;

class ReloadlyGiftCardTransaction extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'product' => 'array',
        'response' => 'array'
    ];

    protected $appends = ['message'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function sender_currency(){
        return $this->belongsTo(AirtimeCurrency::class,'sender_currency_id');
    }

    public function recipient_currency(){
        return $this->belongsTo(AirtimeCurrency::class,'recipient_currency_id');
    }

    public function product(){
        return $this->belongsTo(ReloadlyGiftCardProduct::class,'product_id');
    }

    public function sendTransaction(){
        $credentials = [
            'key' => Setting::get('reloadly_api_key'),
            'secret' => Setting::get('reloadly_api_secret'),
            'mode' => Setting::get('reloadly_api_mode')
        ];
        if (!$credentials['key'] || !$credentials['secret'] || !$credentials['mode']) {
            return ['error'=>'Keys not found in settings.'];
        }
        $reloadly = Reloadly::Make($credentials['key'], $credentials['secret'], $credentials['mode']);
        $credentials['gift_card_token'] = $reloadly->getGiftToken();
        if ($credentials['gift_card_token']) {
            $response = $reloadly->orderReloadlyGiftProducts($this['product']['rid'], $this['product']['country']['isoName'], 1,
                $this['recipient_amount'], $this['reference'], $this['user']['name'], $this['email']);

            if ((isset($response['status'])) && ($response['status'] === 'SUCCESSFUL')) {
                $this['transaction_id'] = $response['transactionId'];
                $this['status'] = 'SUCCESS';
            } else {
                $this['status'] = 'FAIL';
            }
            $this['response'] = $response;
            $this->save();
        }
    }

    public function getMessageAttribute(){
        switch ($this['status']){
            case "PENDING":
                return "Transaction is paid. But its pending transaction. Please wait a few minuites for the status to update.";
            case "SUCCESS":
                return "Transaction completed successfully.";
            case "FAIL":
                return isset($this['response']['message'])?$this['response']['message']: "Transaction Failed. No response";
            case "PENDING_PAYMENT":
                return "Transaction is pending payment";
            case "REFUNDED":
                return "Gift Card Transaction has been refunded. It failed due to Error : ".(isset($this['response']['message'])?$this['response']['message']: "Unknown");
            default:
                return "Error : Unknown Status found.";
        }
    }
}
