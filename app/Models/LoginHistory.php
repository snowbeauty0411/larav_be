<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;
    protected $table = 'login_histories';
    protected $fillable = [
        'block_at',
        'ip_address',
        'count_failed',
    ];

    public function findByIp($ip)
    {
        return $this->where('ip_address',$ip)
                    ->first();
    }
}
