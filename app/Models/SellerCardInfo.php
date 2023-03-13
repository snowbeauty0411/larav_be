<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerCardInfo extends Model
{
    use HasFactory;

    protected $table = 'seller_card_info';

    protected $fillable = [
        'id',
        'bank_name',
        'bank_id',
        'branch_code',
        'account_type',
        'account_number',
        'first_name_account',
        'last_name_account',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function updateById($id, $data)
    {
        $this->where('id', $id)->update($data);
    }

    public function banks()
    {
        return $this->hasOne(Banks::class, 'id', 'bank_id');
    }

    public function branches()
    {
        return $this->hasOne(Branches::class, 'id', 'branch_id');
    }

    public function findBySellerId($seller_id)
    {
        return $this->where('seller_id', $seller_id)->first();
    }
}
