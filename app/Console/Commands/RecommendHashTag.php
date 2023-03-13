<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\BaseController;
use App\Models\RecommendHashTag as ModelsRecommendHashTag;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RecommendHashTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hashtag:recommend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recommend HashTag';

    protected $recommendHashTag;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ModelsRecommendHashTag $recommendHashTag,
        BaseController $baseController
    )
    {
        $this->recommendHashTag = $recommendHashTag;
        $this->baseController = $baseController;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $redis = Redis::connection();

            $hashTags = $this->recommendHashTag->all();
            foreach ($hashTags as $tag) {
                $tag->delete();
            }

            if ($redis->exists('redis_recommend_hashtags')) {
                $redisHashTags =  json_decode($redis->get('redis_recommend_hashtags'), true);
                // Log::debug(json_encode($redisHashTags));
                $redis->del('redis_recommend_hashtags');
                foreach ($redisHashTags as $key => $count) {
                    $tag_id = $key;
                    $this->recommendHashTag->create([
                        'tag_id' => $tag_id,
                        'count' => $count
                    ]);
                }
            }
        }catch (Exception $e) {
            $this->baseController->log("scheduleRecommendHashTags", null, null, $e->getMessage());
        }
    }
}
