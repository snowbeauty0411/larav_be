<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferHistory extends Model
{
    use HasFactory;

    protected $table = 'transfer_history';

    protected $fillable = [
        'id',
        'seller_id',
        'transfer_amount',
        'transfer_fee',
        'status',
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i',
    //     'updated_at' => 'datetime:Y-m-d H:i',
    // ];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'account_id');
    }

    public function sellerCardInfo()
    {
        return $this->belongsTo(SellerCardInfo::class, 'seller_id', 'id');
    }

    public function generateTransferID()
    {
        $random = strval(mt_rand(100000000, 999999999));
        while (!is_null($this->find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }
        return $random;
    }

    public function getAllBySeller($seller_id, $per_page)
    {
        $query = $this->where('seller_id', $seller_id)
                    ->orderBy('created_at', 'DESC');

        if (!$per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($per_page);
        }

        return $results;
    }

    public function findTransferRegisterBySeller($seller_id)
    {
        return $this->where([
                    'seller_id' => $seller_id,
                    'status' => 0,
                    ])->first();
    }

    public function getAll($condition)
    {
        $query = $this->with(['seller', 'sellerCardInfo']);

        if (isset($condition->seller_name)) {
            $query->whereRelation('seller', 'sellers.account_name' , 'like', '%' . $condition->seller_name . '%');
        }
        if (isset($condition->created_at)) {
            $query->whereDate('created_at', $condition->created_at);
        }
        if (isset($condition->status)) {
            $query->where('status', $condition->status);
        }

        $query->orderBy('created_at', 'DESC');

        if (!$condition->per_page) {
            $results = $query->paginate(10);
        } else {
            $results = $query->paginate($condition->per_page);
        }
        return $results;
    }
}
