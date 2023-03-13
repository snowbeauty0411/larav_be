<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingInfo extends Model
{
    use HasFactory;
    protected $table = 'shipping_info';

    protected $fillable = [
        'buyer_id',
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'phone',
        'post_code',
        'address',
        'is_default'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function findByBuyerId($buyer_id)
    {
        return $this->where('buyer_id', $buyer_id)->get();
    }

    public function updateIsDefault($id, $buyer_id)
    {
        return $this->where('buyer_id', $buyer_id)->where('id', '!=', $id)->update([
            'is_default' => 0
        ]);
    }

    public function findDefaultByBuyerId($buyer_id)
    {
        return $this->where([
            'buyer_id' => $buyer_id,
            'is_default' => 1
        ])->first();
    }
}
