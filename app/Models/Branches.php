<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branches extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'bank_id',
        'branch_id',
        'branch_name'
    ];

    public function getBranch($request) 
    {
        $query = $this->where('bank_id', $request->bank_id);
        if ($request->name) {
            $query = $query->where('branch_name', 'like', '%' . $request->name . '%');
        }

        $result = $query->get();

        return $result;
    }
}
