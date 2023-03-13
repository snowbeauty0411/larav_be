<?php

namespace App\Models;

use App\Constants\UserConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RecommendService extends Model
{
    use HasFactory;

    protected $table = 'recommend_services';

    protected $fillable = [
        'service_id',
        'buyer_id',
        'count',
    ];

    
    public function redisCountRecommendService($services, $buyer_id)
    {
        $redis = Redis::connection();
        if ($redis->exists('redis_recommend_services')) {
            $redisServices =  json_decode($redis->get('redis_recommend_services'), true);
        } else {
            $redisServices = [];
        }
        
        foreach ($services as $service) {
            $key = $buyer_id. '-' .$service->id;
            $redisServices[$key] =  isset($redisServices[$key]) ?  $redisServices[$key] + 1 : 1;
        }
        $redis->set('redis_recommend_services', json_encode($redisServices));
    }

    public function findByServiceAndBuyer($service_id, $buyer_id)
    {
        return $this->where([
            'service_id' => $service_id,
            'buyer_id' => $buyer_id
        ])->first();
    }
}
