<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FavoriteTag extends Model
{
    use HasFactory;

    protected $table = 'favorite_tags';

    protected $fillable = [
        'name'
    ];

    public function service()
    {
        return $this->hasMany(ServiceTag::class, 'favorite_tag_id', 'id');
    }

    public function getByName($name)
    {
        return $this->where('name', $name)->first();
    }

    public function deleteFavoriteTag($id)
    {
        $this->where('id', $id)->delete();
    }

    public function getAllTagForAdmin($per_page, $condition)
    {
        $query = FavoriteTag::select([
                        DB::raw('favorite_tags.id as id'),
                        DB::raw('favorite_tags.name as name'),
                        DB::raw('count(service_favorite_tags.favorite_tag_id) as amount_registed'),
                        DB::raw('favorite_tags.created_at as created_at'),
                        DB::raw('favorite_tags.updated_at as updated_at'),
                        ])
                    ->leftJoin('service_favorite_tags', 'service_favorite_tags.favorite_tag_id', 'favorite_tags.id')
                    ->groupBy('id');

        //ID
        if (isset($condition->id)) {
            $query->having('id', 'like', '%' . $condition->id . '%');
        }

        //name
        if (isset($condition->name)) {
            $query->having('name', 'like', '%' . $condition->name . '%');
        }

        //amount_registed
        if (isset($condition->amount_registed)) {
            $query->having('amount_registed', 'like', '%' . $condition->amount_registed . '%');
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
                $query->orderBy('name', $type);
            } elseif ($condition->sort === 3) {
                $query->orderBy('amount_registed', $type);
            } elseif ($condition->sort === 4) {
                $query->orderBy('created_at', $type);
            } elseif ($condition->sort === 5) {
                $query->orderBy('updated_at', $type);
            }
        } else {
            $query->orderBy('amount_registed', 'DESC');
        }

        if (!$per_page) {
            $results = $query->paginate(50);
        } else {
            $results = $query->paginate($per_page);
        }

        return $results;
    }

    public function getAllTag()
    {
        $results = FavoriteTag::select([
                        DB::raw('favorite_tags.id as id'),
                        DB::raw('favorite_tags.name as name'),
                        DB::raw('count(service_favorite_tags.favorite_tag_id) as amount_registed'),
                        DB::raw('favorite_tags.created_at as created_at'),
                        DB::raw('favorite_tags.updated_at as updated_at'),
                        ])
                    ->leftJoin('service_favorite_tags', 'service_favorite_tags.favorite_tag_id', '=', 'favorite_tags.id')
                    ->groupBy('id')
                    ->orderBy('amount_registed', 'DESC')
                    ->get();
        return $results;
    }
}
