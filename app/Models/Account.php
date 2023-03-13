<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;

class Account extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'email_verify_token',
        'email_verify_token_expiration',
        'email_verified_at',
        'classification_id',
        'person',
        'business_format',
        'is_blocked',
        'blocked_at',
        'last_login_at',
        'date_entry',
        'date_withdrawal',
        'deleted_at',
        'postcode',
        'address_pref',
        'address_city',
        'address_other1',
        'address_other2',
        'phone_number',
        'birth_day',
        'admin_check_date',
        'identity_verification_status',
        'message_mail_flg',
        'transaction_mail_flg',
        'favorite_service_mail_flg',
        'recommend_service_mail_flg',
        'system_maintenance_mail_flg'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function sellers()
    {
        return $this->hasOne(Seller::class);
    }

    public function buyers()
    {
        return $this->hasOne(Buyer::class);
    }

    public function updateAccount($id, $account)
    {
        return $this->where('id', $id)->update($account);
    }

    public function files()
    {
        return $this->hasMany(VerifyAccountIdentity::class);
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

        while (!is_null(Account::find($random))) {
            $random = strval(mt_rand(100000000, 999999999));
        }

        return $random;
    }

    public function createAccount()
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

    public function getInfoAccountByEmail($email)
    {
        $result = $this->with('buyers', 'sellers')->where('email', $email)
            ->first();
        return $result;
    }

    // public function getInfoAccountByAccountName($name)
    // {
    //     $result = $this->where('account_name', $name)
    //         ->first();
    //     return $result;
    // }

    public function getInfoAccountById($id)
    {
        $result = $this->with('buyers', 'sellers')->where('id', $id)
            ->first();
        return $result;
    }

    public function updatePasswordByAccountId($id, $password)
    {
        $result = $this->where('id', $id)
            ->update(['password' => Hash::make($password)]);
        return $result;
    }

    public function updateEmailByAccountId($id, $email)
    {
        $result = $this->where('id', $id)
            ->update(['email' => $email]);
        return $result;
    }

    public function getAllBuyers($per_page, $condition)
    {
        $query = $this
            ->select(
                'id',
                'email',
                'is_blocked',
                'date_withdrawal',
                'reason_withdrawal',
                'admin_check_date',
                'identity_verification_status',
                'phone_number'
            )->with('buyers');

        //ID
        if (isset($condition->id)) {
            $query->where('id', 'like', '%' . $condition->id . '%');
        }

        //email
        if (isset($condition->email)) {
            $query->where('email', 'like', '%' . $condition->email . '%');
        }

        //phone
        if (isset($condition->phone_number)) {
            $query->where('phone_number', 'like', '%' . $condition->phone_number . '%');
        }

        //full name
        if (isset($condition->full_name)) {
            $query->whereHas('buyers', function ($q) use ($condition) {
                $q->where('last_name', 'like', '%' . $condition->full_name . '%')
                    ->orWhere('first_name', 'like', '%' . $condition->full_name . '%')
                    ->orWhere(DB::raw('CONCAT(last_name," ",first_name)'), 'like', '%' . $condition->full_name . '%')
                    ->orWhere(DB::raw('CONCAT(first_name," ",last_name)'), 'like', '%' . $condition->full_name . '%');
            });
        }

        //phone
        if (isset($condition->account_name)) {
            $query->whereHas('buyers', function ($q) use ($condition) {
                $q->where('account_name', 'like', '%' . $condition->account_name . '%');
            });
        }
        //Identity verification status
        if ($condition->identification_verify_status) {
            $query->where('identity_verification_status', $condition->identification_verify_status);
        }

        //blocked
        if ($condition->is_blocked) {
            $query->where('is_blocked', $condition->is_blocked);
        }
        //sort
        if (isset($condition->sort) && isset($condition->sort_type)) {
            $type = 'ASC';
            if ($condition->sort_type === 2) {
                $type = 'DESC';
            }
            if ($condition->sort === 1) {
                $query->orderBy('id', $type);
            } elseif ($condition->sort === 2) {
                $query->orderBy('buyers.first_name', $type)
                    ->orderBy('buyers.last_name', $type);
            } elseif ($condition->sort === 3) {
                $query->orderBy('buyers.name', $type);
            } elseif ($condition->sort === 4) {
                $query->orderBy('email', $type);
            } elseif ($condition->sort === 5) {
                $query->orderBy('phone_number', $type);
            }
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        if (!$per_page) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($per_page);
        }

        return $results;
    }

    public function getAllSellers($per_page, $condition)
    {
        $query = $this
            ->select(
                'id',
                'email',
                'is_blocked',
                'date_withdrawal',
                'reason_withdrawal',
                'admin_check_date',
                'identity_verification_status',
                'phone_number'
            )->with('sellers');

        //ID
        if (isset($condition->id)) {
            $query->where('id', 'like', '%' . $condition->id . '%');
        }

        //email
        if (isset($condition->email)) {
            $query->where('email', 'like', '%' . $condition->email . '%');
        }

        //phone
        if (isset($condition->phone_number)) {
            $query->where('phone_number', 'like', '%' . $condition->phone_number . '%');
        }

        //full name
        if (isset($condition->full_name)) {
            $query->whereHas('sellers', function ($q) use ($condition) {
                $q->where('last_name', 'like', '%' . $condition->full_name . '%')
                    ->orWhere('first_name', 'like', '%' . $condition->full_name . '%')
                    ->orWhere(DB::raw('CONCAT(last_name," ",first_name)'), 'like', '%' . $condition->full_name . '%')
                    ->orWhere(DB::raw('CONCAT(first_name," ",last_name)'), 'like', '%' . $condition->full_name . '%');
            });
        }

        //phone
        if (isset($condition->account_name)) {
            $query->whereHas('sellers', function ($q) use ($condition) {
                $q->where('account_name', 'like', '%' . $condition->account_name . '%');
            });
        }
        //Identity verification status
        if ($condition->identification_verify_status) {
            $query->where('identity_verification_status', $condition->identification_verify_status);
        }

        //blocked
        if ($condition->is_blocked) {
            $query->where('is_blocked', $condition->is_blocked);
        }
        //sort
        if (isset($condition->sort) && isset($condition->sort_type)) {
            $type = 'ASC';
            if ($condition->sort_type === 2) {
                $type = 'DESC';
            }
            if ($condition->sort === 1) {
                $query->orderBy('id', $type);
            } elseif ($condition->sort === 2) {
                $query->orderBy('sellers.first_name', $type)
                    ->orderBy('sellers.last_name', $type);
            } elseif ($condition->sort === 3) {
                $query->orderBy('sellers.name', $type);
            } elseif ($condition->sort === 4) {
                $query->orderBy('email', $type);
            } elseif ($condition->sort === 5) {
                $query->orderBy('phone_number', $type);
            }
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        if (!$per_page) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($per_page);
        }

        return $results;
    }

    public function findByAccountBuyer($id)
    {
        $data = $this->with('buyers')->where('id', $id)->first();
        if (isset($data['buyers']) && $data['buyers']['profile_image_url_buy']) {
            $data['buyers']['profile_image_url_buy'] = route('getAvatar', ['file_name' => $data['buyers']['profile_image_url_buy']]);
        }
        return $data;
    }

    public function findAccountSeller($id)
    {
        $data = $this->with('sellers')->where('id', $id)->first();
        if (isset($data['sellers']) && $data['sellers']['profile_image_url_sell']) {
            $data['sellers']['profile_image_url_sell'] = route('getAvatar', ['file_name' => $data['sellers']['profile_image_url_sell']]);
        }
        return $data;
    }

    // public function getAccountName($id)
    // {
    //     $result = $this->select('accounts.account_name')->where('id', $id)->first();
    //     return $result;
    // }

    public function getTypeAccount($id)
    {
        $data = $this->with('buyers', 'sellers')->where('id', $id)->first();
        if (isset($data['buyers']) && isset($data['sellers'])) {
            return 'BUYER';
        } else if (isset($data['sellers'])) {
            return 'SELLER';
        } else if (isset($data['buyers'])) {
            return 'BUYER';
        }
    }

    public function accountInfo($id)
    {
        $account =  $this->with('buyers', 'sellers', 'files')->where('id', $id)->first();

        if (isset($account) && count($account['files']) > 0) {
            foreach ($account['files'] as $key => $file) {
                $account['files'][$key]['path_file1'] = route('getResourcePrivateFile', ['account_id' => $id, 'fileName' => $file['file1'], 1]);
                $account['files'][$key]['path_file2'] = route('getResourcePrivateFile', ['account_id' => $id, 'fileName' => $file['file2'], 2]);
            }
        }
        return $account;
    }

    public function getSampleInfoAccount($id)
    {

        $result = $this->where('id', $id)
            ->select(['accounts.id',
                'accounts.email',
                'accounts.phone_number',
                'accounts.birth_day',
                'accounts.identity_verification_status',
                'accounts.message_mail_flg',
                'accounts.transaction_mail_flg',
                'accounts.favorite_service_mail_flg',
                'accounts.recommend_service_mail_flg',
                'accounts.system_maintenance_mail_flg'])
            ->with(['buyers:account_id,account_name', 'sellers:account_id,account_name'])
            ->first();

        return $result;
    }

    public function checkEmailUsed($email)
    {
        return $this->where('email', $email)->first();
    }
}
