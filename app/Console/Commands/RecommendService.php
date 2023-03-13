<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\BaseController;
use App\Models\NumberAccessListServicePage;
use App\Models\RecommendService as ModelsRecommendService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RecommendService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:recommend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recommend Service';

    protected $numberAccessListServicePage;
    protected $recommendService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        NumberAccessListServicePage $numberAccessListServicePage,
        ModelsRecommendService $recommendService,
        BaseController $baseController
        )
    {
        $this->numberAccessListServicePage = $numberAccessListServicePage;
        $this->recommendService = $recommendService;
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
        try{
            $redis = Redis::connection();
            if ($redis->exists('redis_service_access')){
                $redisServices =  json_decode($redis->get('redis_service_access'), true);
                // Log::debug(json_encode($redisServices));
                $redis->del('redis_service_access');
                foreach ($redisServices as $key => $count) {
                    $number_access_list_service =  $this->numberAccessListServicePage->findLastByService($key);
                    if (!$number_access_list_service) {
                        $this->numberAccessListServicePage->create([
                            'service_id' => $key,
                            'count_by_month' => $count
                        ]);
                    } else {
                        $created_date = Carbon::parse($number_access_list_service->created_at);
                        if ($created_date->isCurrentDay()) {
                            $number_access_list_service->update([
                                'count_by_month' => $number_access_list_service->count_by_month + $count
                            ]);
                        } else {
                            $this->numberAccessListServicePage->create([
                                'service_id' => $key,
                                'count_by_month' => $count
                            ]);
                        }
                    }
                }
            }

            if ($redis->exists('redis_recommend_services')) {
                $redisServices =  json_decode($redis->get('redis_recommend_services'), true);
                // Log::debug(json_encode($redisServices));
                $redis->del('redis_recommend_services');
                foreach ($redisServices as $key => $count) {
                    $parts = explode('-', $key);
                    if (count($parts) == 2){
                        $buyer_id = (int) $parts[0];
                        $service_id = (int) $parts[1];
                        $recommendService =  $this->recommendService->findByServiceAndBuyer($service_id, $buyer_id);
                        if (!$recommendService) {
                            $this->recommendService->create([
                                'service_id' => $service_id,
                                'buyer_id' => $buyer_id,
                                'count' => $count
                            ]);
                        } else {
                            $recommendService->update([
                                'count' => $recommendService->count + $count
                            ]);
                        }
                    }
                }
            }
        } catch( Exception $e ) {
            $this->baseController->log("scheduleRecommendServices", null, null, $e->getMessage());
        }
    }
}
