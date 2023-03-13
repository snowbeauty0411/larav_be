<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        // 保存イベント
        static::saving(function ($user) {
            // 郵便場脳からハイフン削除

            // パスワードをハッシュ化
            if (isset($user->password)) {
                $user->password = Hash::make($user->password);
            }

            // 新規登録時、IDを自動採番
            if (!isset($user->id)) {
                $user->id = self::generateId();
            }
        });

        // 削除イベント
        static::deleting(function ($user) {
            // マスク処理
            self::maskProcess();
        });
    }

    /**
     * ID発行
     */
    private static function generateId()
    {
        // 9桁のランダムな数字（0始まり禁止）
        $random = strval(mt_rand(100000000, 999999999));

        while (!is_null(Admin::find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }

        return $random;
    }

    /**
     * データマスク処理
     */
    private static function maskProcess(Admin $user)
    {
        $prefix = $user->id . '_system_deleted_';

        $user->email = $prefix . $user->email;  // メールアドレス
        $user->save();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function createAdmin()
    {
        error_log($this->password);

        if (isset($this->password)) {
            $this->password = Hash::make($this->password);
        }

        // 新規登録時、IDを自動採番
        if (!isset($this->id)) {
            $this->id = self::generateId();
        }
        $this->save();
    }

    public function getInfor()
    {
        return $this->where('id', '=', '5')->get();
    }

    public function getAdmin()
    {
        return $this->get();
    }

    public function info($id)
    {
        return DB::table('admins')->select('admin.name')->where('id', $id)->first();
    }

    public function adminInfo()
    {
        return $this->first();
    }
}
