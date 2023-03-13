<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    use HasFactory;
    protected $table = 'message_threads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'admin_id',
        'created_at',
        'updated_at',
    ];

    public function buyers() {
        return $this->hasOne(Buyer::class, 'account_id', 'buyer_id');
    }
    
    public function sellers() {
        return $this->hasOne(Seller::class, 'account_id', 'seller_id');
    }
    
    public function admins() {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }

    public function getAllThreadBuyer($buyer_id, $condition)
    {
        $query = $this->with('sellers', 'admins')
            ->select(
                'message_threads.id',
                'message_threads.seller_id',
                'message_threads.admin_id',
            )->where('message_threads.buyer_id', $buyer_id);
        //Filter 
        if (isset($condition->keyword)) {
            $keyword = $condition->keyword;
            $query->where(function ($query) use ($keyword) {
                $query->whereHas('sellers', function($q) use($keyword) {
                            $q->where('account_name', 'like', '%' . $keyword . '%');
                            })
                    ->orWhereHas('admins', function($q) use($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%');
                            });
            });
        }
        if (isset($condition->per_page)) {
            $result = $query->orderBy('message_threads.updated_at', 'DESC')->paginate($condition->per_page);
        } else {
            $result = $query->orderBy('message_threads.updated_at', 'DESC')->get();
        }

        return $result;
    }

    public function getAllThreadSeller($seller_id, $condition)
    {
        $query = $this->with('buyers', 'admins')
            ->select(
                'message_threads.id',
                'message_threads.buyer_id',
                'message_threads.admin_id',
            )->where('message_threads.seller_id', $seller_id);

        //Filter 
        if (isset($condition->keyword)) {
            $keyword = $condition->keyword;
            $query->where(function ($query) use ($keyword) {
                $query->whereHas('buyers', function($q) use($keyword) {
                            $q->where('account_name', 'like', '%' . $keyword . '%');
                            })
                    ->orWhereHas('admins', function($q) use($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%');
                            });
            });
        }

        if (isset($condition->per_page)) {
            $result = $query->orderBy('message_threads.updated_at', 'DESC')->paginate($condition->per_page);
        } else {
            $result = $query->orderBy('message_threads.updated_at', 'DESC')->paginate(50);
        }

        return $result;
    }

    // public function checkExistByBuyer($user_id, $thread_id)
    // {
    //     $result = $this->where('id', $thread_id)->where('buyer_id', $user_id)->first();
    //     return $result;
    // }

    // public function checkExistBySeller($user_id, $thread_id)
    // {
    //     $result = $this->where('id', $thread_id)->where('seller', $user_id)->first();
    //     return $result;
    // }

    public function checkThreadExist($thread_id)
    {
        $query = $this->where('id', $thread_id);
        $result = $query->first();
        return $result;
    }

    public function getAllThreadByAdmin($admin_id, $condition)
    {
        $query = $this->with('buyers', 'sellers')
            ->select(
                'message_threads.id',
                'message_threads.buyer_id',
                'message_threads.seller_id',
            )
            ->where('message_threads.admin_id', $admin_id);
        //filter
        if (isset($condition->keyword)) {
            $keyword = $condition->keyword;
            $query->where(function ($query) use ($keyword) {
                $query->whereHas('buyers', function($q) use($keyword) {
                            $q->where('account_name', 'like', '%' . $keyword . '%');
                            })
                    ->orWhereHas('sellers', function($q) use($keyword) {
                            $q->where('account_name', 'like', '%' . $keyword . '%');
                            });
            });
        }
        $per_page = $condition->per_page ?? 50;
        $result = $query->orderBy('message_threads.updated_at', 'DESC')->paginate($per_page);
        return $result;
    }

    public function checkExistByUser($buyer_id, $seller_id)
    {
        $result = $this->where('buyer_id', $buyer_id)->where('seller_id', $seller_id)->first();
        return $result;
    }

    public function checkExistWithAdminBySeller($admin_id, $seller_id)
    {
        $result = $this->where('admin_id', $admin_id)->where('seller_id', $seller_id)->first();
        return $result;
    }

    public function checkExistWithAdminByBuyer($admin_id, $buyer_id)
    {
        $result = $this->where('admin_id', $admin_id)->where('buyer_id', $buyer_id)->first();
        return $result;
    }
}
