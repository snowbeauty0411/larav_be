<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'content',
        'status'
    ];

    public function getAllContactByAdmin($condition)
    {
        $query = $this->select('*');

        //ID
        if (isset($condition->id)) {
            $query->where('id', 'like', '%' . $condition->id . '%');
        }
        //name
        if (isset($condition->name)) {
            $query->where('name', 'like', '%' . $condition->name . '%');
        }
        //email
        if (isset($condition->email)) {
            $query->where('email', 'like', '%' . $condition->email . '%');
        }
        //status
        if (isset($condition->status)) {
            $query->where('status', $condition->status);
        }

        //sort
        if (isset($condition->sort)) {
            $type = 'ASC';
            if (isset($condition->sort_type)&&$condition->sort_type === 2) {
                $type = 'DESC';
            }
            if ($condition->sort === 1) {
                $query->orderBy('id', $type);
            } elseif ($condition->sort === 2) {
                $query->orderBy('name', $type);
            } elseif ($condition->sort === 3) {
                $query->orderBy('email', $type);
            } elseif ($condition->sort === 4) {
                $query->orderBy('status', $type);
            }
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        if (!isset($condition->per_page)) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($condition->per_page);
        }

        return $results;
    }

    public function findById($id){
        $result=$this->where('id',$id)->first();
        return $result;
    }
}
