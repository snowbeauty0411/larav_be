<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\Area;
use App\Models\Buyer;
use App\Models\NumberAccessListServicePage;
use App\Models\Prefecture;
use App\Models\RecommendService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EkispertController extends BaseController
{
    protected $service;
    protected $serviceArea;
    protected $prefecture;
    protected $area;
    protected $numberAccessListServicePage;
    protected $recommendService;
    protected $buyer;

    public function __construct(
        Service $service,
        ServiceArea $serviceArea,
        Prefecture $prefecture,
        Area $area,
        NumberAccessListServicePage $numberAccessListServicePage,
        RecommendService $recommendService,
        Buyer $buyer
    ) {
        $this->service = $service;
        $this->serviceArea = $serviceArea;
        $this->prefecture = $prefecture;
        $this->area = $area;
        $this->numberAccessListServicePage = $numberAccessListServicePage;
        $this->recommendService = $recommendService;
        $this->buyer = $buyer;
    }


    /**
     *   @OA\Get(
     *     path="/api/ekispert-station",
     *     summary="Suggestion station by keyword",
     *     tags={"Ekispert Api"},
     *     @OA\Parameter(
     *         description="station name or place",
     *         in="query",
     *         name="name",
     *         example="神奈川",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="limit need to input info",
     *         in="query",
     *         name="limit",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = ['key' => config('services.ekispert.key')];
            if (isset($request->name)) $query['name'] = $request->name;
            if (isset($request->code)) $query['code'] = $request->code;
            if (isset($request->oldName)) $query['oldName'] = $request->oldName;
            if (isset($request->railName)) $query['railName'] = $request->railName;
            if (isset($request->prefectureCode)) $query['prefectureCode'] = $request->prefectureCode;
            if (isset($request->corporationName)) $query['corporationName'] = $request->corporationName;
            if (isset($request->operationLineCode)) $query['operationLineCode'] = $request->operationLineCode;
            $query['type'] = isset($request->type) ? $request->type : 'train';
            $query['direction'] = isset($request->direction) ? $request->direction : 'up';
            $query['offset'] = isset($request->offset) ? $request->offset : 1;
            $query['limit'] = isset($request->limit) ? $request->limit : 20;
            $query['gcs'] = isset($request->gcs) ? $request->limit : 'tokyo';

            $response = Http::get('https://api.ekispert.jp/v1/json/station', $query);
            $data = $response->json();
            if (!isset($data['ResultSet']['Point'])) {
                $data['ResultSet']['Point'] = null;
            } else {
                if ($data['ResultSet']['max'] == 1) {
                    $code = $data['ResultSet']['Point']['Station']['code'];
                    $station_info = $this->getStationInfo($code);
                    if (isset($station_info['ResultSet']['Information'])) {
                        $data['ResultSet']['Point']['Line'] = $station_info['ResultSet']['Information']['Line'];
                    }
                } else {
                    foreach ($data['ResultSet']['Point'] as $key => $point) {
                        $code = $point['Station']['code'];
                        $station_info = $this->getStationInfo($code);
                        if (isset($station_info['ResultSet']['Information']['Line'])) {
                            $data['ResultSet']['Point'][$key]['Line'] = $station_info['ResultSet']['Information']['Line'];
                        }
                    }
                }
            }
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            error_log($e);
            $this->log("ekispert_index", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getServiceArea(Request $request)
    {
        try {
            $service_ids = $this->serviceArea->getIdService($request);
            $service = $this->service->getServiceByArea($request->size_page, $service_ids);
            return $this->sendSuccessResponse($service);
        } catch (Exception $e) {
            $this->log("ekispert_getServiceArea", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getStationInfo($code)
    {
        $query = [
            'key' => config('services.ekispert.key'),
            'code' => $code,
            'type' => 'operationLine'
        ];
        $response = Http::get('https://api.ekispert.jp/v1/json/station/info', $query);
        return $response->json();
    }


    /**
     *   @OA\Get(
     *     path="/api/ekispert/station-area/service-list/{page}",
     *     summary="Search service by area of station",
     *     tags={"Ekispert Api"},
     *     @OA\Parameter(
     *         description="size number need to input info",
     *         in="path",
     *         name="page",
     *         example="10",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="lat of station",
     *         in="query",
     *         name="lat",
     *         example="神奈川",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="lng of station",
     *         in="query",
     *         name="lng",
     *         example="神奈川",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Parameter(
     *         description="0=>並び替え, 1=>新着, 2=>評価が高い, 3=>価格が高い, 4=>価格が安い, 5=>登録者が多い",
     *         in="query",
     *         name="sort",
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="Buyer ID need to input info",
     *         in="query",
     *         name="buyer_id",
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     */
    public function getServiceAreaByStation($page = 10, Request $request)
    {
        try {
            $services = $this->service->searchServiceByStation($page, $request);

            $this->numberAccessListServicePage->redisCountNumberAccessServiceList($services);

            if ($request->buyer_id && $this->buyer->findByAccountId($request->buyer_id)) {
                $this->recommendService->redisCountRecommendService($services, $request->buyer_id);
            }
            Log::debug(Redis::get('redis_service_access'));
            return $this->sendSuccessResponse($services);
        } catch (Exception $e) {
            error_log($e);
            $this->log("getServiceAreaByStation", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);           
        }
    }
}
