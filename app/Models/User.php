<?php

namespace App\Models;

use App\Constants\UserConst;
use App\Traits\ZipcodeTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;


use function PHPUnit\Framework\isEmpty;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;
    // use ZipcodeTrait;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];


    public function updateUser($id, $user)
    {
        $this->where('id', $id)->update($user);
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

        /**
     * ID発行
     */
    private static function generateId()
    {
        // 9桁のランダムな数字（0始まり禁止）
        $random = strval(mt_rand(100000000, 999999999));

        while (!is_null(User::find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }

        return $random;
    }

    public function createUser()
    {
        // 新規登録時、IDを自動採番
        if (!isset($this->id)) {
            $this->id = self::generateId();
        }
        return $this->save();
    }

    
    /**
     * モデルのイベント時起動処理
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // 保存イベント
        static::saving(function ($user) {
            // パスワードをハッシュ化
            if (isset($user->password)) {
                $user->password = Hash::make($user->password);
            }
        });

        // 削除イベント
        static::deleting(function ($user) {
            // マスク処理
            self::maskProcess();
        });
    }

    public function getInfoUserByEmail($email)
    {
        $result = $this->where('email', $email)
            ->first();
        return $result;
    }

    public function updatePasswordByUserId($id, $password)
    {
        $result = $this->where('id', $id)
            ->update(['password' => Hash::make($password)]);
        return $result;
    }

    public function findUserById($id)
    {
        return $this->find($id);
    }
}
