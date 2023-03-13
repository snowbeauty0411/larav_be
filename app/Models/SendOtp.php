<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendOtp extends Model
{
    use HasFactory;

    protected $table = 'send_otp';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'email',
        'otp_type',
        'otp',
        'otp_expire_at',
    ];

    public function getByUserId($account_id)
    {
        $result = $this->where('account_id', $account_id)->first();
        return $result;
    }

    public function getByOTP($otp, $otp_type)
    {
        return $this->where([
                    'otp' => $otp,
                    'otp_type' => $otp_type
                ])->first();
    }

    public function getByEmail($email, $otp_type)
    {
        return $this->where([
                    'email' => $email,
                    'otp_type' => $otp_type
                ])->first();
    }

    public function generateOtp()
    {
        $random = strval(mt_rand(100000, 999999));
        while (!is_null($this->find($random))) {
            $random = strval(mt_rand(100000, 999999));
        }
        return $random;
    }


}
