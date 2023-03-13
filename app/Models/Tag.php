<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    public function serviceTag()
    {
        return $this->hasMany(ServiceTag::class);
    }

    public function getByName($name)
    {
        return $this->where(['name' => $name])->first();
    }

    // public function getTopTag($request)
    // {
    //     if (isset($request->name)) {
    //         return $this->select('*', 'recommend_hashtags.count as tag_count')
    //                     ->leftjoin('recommend_hashtags', 'recommend_hashtags.tag_id', '=', 'tags.id')
    //                     ->orderBy('tag_count', 'DESC')
    //                     ->where('name', 'like', '%' . $request->name . '%')
    //                     ->get();
    //     } else {
    //         return $this->select('*', 'recommend_hashtags.count as tag_count')
    //                     ->leftjoin('recommend_hashtags', 'recommend_hashtags.tag_id', '=', 'tags.id')
    //                     ->orderBy('tag_count', 'DESC')
    //                     ->limit(18)
    //                     ->get();
    //     }
    // }
}
