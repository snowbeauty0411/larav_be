<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailReset extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'email_resets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'new_email',
        'auth_key',
        'expiration',
        'created_at'
    ];

    // Get by auth key.
    public function findByAuthKey($auth_key)
    {
        return $this
            ->where('auth_key', $auth_key)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
