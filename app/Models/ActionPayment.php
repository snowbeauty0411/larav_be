<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPayment extends Model
{
    use HasFactory;

    protected $table = 'action_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shipping_info_id',
        'service_store_buyer_id',
        'delivery_address',
        'buyer_full_name',
        'card_id',
        'skip',
        'charge_at',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function serviceStoreBuyer()
    {
        return $this->belongsTo(ServiceStoreBuyer::class, 'service_store_buyer_id');
    }

    public function findByBuyerIdAndCardId($buyer_id, $card_id)
    {
        return $this->where('card_id', $card_id)
                    ->with('serviceStoreBuyer')
                    ->whereRelation('serviceStoreBuyer', 'service_store_buyers.buyer_id', '=', $buyer_id)
                    ->first();
    }

    public function findByChargeAt($current_date)
    {
        return $this->whereDate('charge_at', '<=', $current_date)->get();
    }

    public function updateActionPayment($id, $action_payment)
    {
        return $this->where('id', $id)->update($action_payment);
    }

    public function getByStoreBuyerId($store_buyer_id)
    {
        return $this->where('service_store_buyer_id', $store_buyer_id)->first();
    }
}
