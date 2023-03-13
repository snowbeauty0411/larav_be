<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $table = 'sellers';

    protected $fillable = [
        'account_id',
        'account_name',
        'first_name',
        'last_name',
        'business_id',
        'url_official_id',
        'profile_text_sell',
        'profile_image_url_sell',
        'stripe_connect_id',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function sellerCardInfo()
    {
        return $this->hasOne(SellerCardInfo::class, 'id', 'account_id');
    }

    public function updateSeller($id, $seller)
    {
        return $this->where('account_id', $id)
            ->update($seller);
    }

    public function getProfileSeller($id)
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
