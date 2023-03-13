<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $table = 'buyers';

    protected $fillable = [
        'account_id',
        'account_name',
        'first_name',
        'last_name',
        'profile_text_buy',
        'profile_image_url_buy',
        'stripe_customer_id',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function updateBuyer($id, $buyer)
    {
        return $this->where('account_id', $id)
            ->update($buyer);
    }

    public function shipping_info_default() {
        return $this->hasOne(ShippingInfo::class, 'buyer_id', 'account_id')->where('is_default', 1);
    }

    public function shipping_info() {
        return $this->hasMany(ShippingInfo::class, 'buyer_id', 'account_id');
    }

    public function getProfileBuyer($id)
    {
        $result = $this->where('account_id', $id)->with('account')
            ->whereRelation('account', 'accounts.is_blocked', '=', 0)
            ->first();
        return $result;
    }

    public function findByAccountId($id)
    {
        return $this->where('account_id', $id)->first();
    }

}
