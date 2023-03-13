<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RecommendHashTag extends Model
{
    use HasFactory;

    protected $table = 'recommend_hashtags';

    protected $fillable = [
        'tag_id',
        'count',
    ];

    
    public function redisCountRecommendHashTag($tag_id)
    {
        $redis = Redis::connection();
        if ($redis->exists('redis_recommend_hashtags')) {
            $redisHashTags =  json_decode($redis->get('redis_recommend_hashtags'), true);
        } else {
            $redisHashTags = [];
        }
        
        $key = $tag_id;
        $redisHashTags[$key] =  isset($redisHashTags[$key]) ?  $redisHashTags[$key] + 1 : 1;
        $redis->set('redis_recommend_hashtags', json_encode($redisHashTags));
    }

    public function findByHashTag($tag_id)
    {
        return $this->where([
            'tag_id' => $tag_id,
        ])->first();
    }

    public function getTopTag($request)
    {
        if (isset($request->name)) {
            return $this->select('*', 'recommend_hashtags.count as tag_count')
                        ->leftjoin('tags', 'recommend_hashtags.tag_id', '=', 'tags.id')
                        ->orderBy('tag_count', 'DESC')
                        ->where('name', 'like', '%' . $request->name . '%')
                        ->get();
        } else {
            return $this->select('*', 'recommend_hashtags.count as tag_count')
                        ->leftjoin('tags', 'recommend_hashtags.tag_id', '=', 'tags.id')
                        ->orderBy('tag_count', 'DESC')
                        ->limit(18)
                        ->get();
        }
    }
}
