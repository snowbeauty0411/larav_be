<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyAccountIdentity extends Model
{
    use HasFactory;
    protected $table = 'verify_account_identities';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'type_id',
        'file1',
        'file2',
        'approval_at',
        'denial_date',
        'delete_date',
    ];

    public function deleteFileByFile($file)
    {
        $this->where('file', $file)
            ->delete();
    }

    public function userFiles($account_id)
    {
        $result = $this->where('account_id', $account_id)
            ->select(
                'verify_buyer_identities.account_id',
                'verify_buyer_identities.file',
            )
            ->get();
        return $result;
    }

    public function getAllFilesByAccountId($account_id)
    {
        return $this->where('account_id', $account_id)->first();
    }

    public function getAllFilesByAccountIdAndUserType($account_id, $user_type)
    {
        return $this->where('account_id', $account_id)->where('user_type', $user_type)->first();
    }

    public function updateVerifyAccountIdentity($id, $verifyBuyerIdentity)
    {
        $this->where('id', $id)->update($verifyBuyerIdentity);
    }

    public function deleteFileByAccountId($account_id)
    {
        $this->where('account_id', $account_id)->delete();
    }

    public function deleteFileByDeleteDate($delete_date)
    {
        $this->where('delete_date', '<=', $delete_date)->delete();
    }

    public function findBuyDeleteDate($delete_date)
    {
        return $this->whereDate('delete_date', '<=', $delete_date)->get();
    }

    public function deleteFileByFileName($file_name)
    {
        return $this->where('file', $file_name)->delete();
    }

    public function getFile1ByAccountIdAndFileName($account_id, $file_name)
    {
        return $this->where('account_id', $account_id)->where('file1', $file_name)->first();
    }

    public function getFile2ByAccountIdAndFileName($account_id, $file_name)
    {
        return $this->where('account_id', $account_id)->where('file2', $file_name)->first();
    }
}
