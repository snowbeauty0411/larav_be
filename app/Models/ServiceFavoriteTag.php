<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFavoriteTag extends Model
{
    use HasFactory;
    protected $table = 'service_favorite_tags';

    protected $fillable = [
        'service_id',
        'favorite_tag_id'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    public function deleteFavoriteServiceTag($id)
    {
        $this->where('id', $id)->delete();
    }

    public function deleteByServiceId($service_id)
    {
        $this->where('service_id' , $service_id)->delete();
    }

    public function deleteByServiceIdAndFavoriteTagId($service_id, $favorite_tag_id)
    {
        $this->where('service_id', $service_id)->where('favorite_tag_id', $favorite_tag_id)->delete();
    }

    public function findByServiceIdAndFavoriteTagId($service_id, $favorite_tag_id)
    {
        return $this->where('service_id', $service_id)
            ->where('favorite_tag_id', $favorite_tag_id)->first();
    }

    public function getByFavoriteTagId($favorite_tag_id)
    {
        return $this->where('favorite_tag_id', $favorite_tag_id)->get();
    }
}
