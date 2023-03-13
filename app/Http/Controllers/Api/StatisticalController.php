<?php

namespace App\Http\Controllers\Api;

use App\Constants\UserConst;
use App\Models\BuyerServiceReserve;
use App\Models\Delivery;
use App\Models\Favorite;
use App\Models\NumberAccessListServicePage;
use App\Models\NumberAccessServiceDetailPage;
use App\Models\NumberClickOfficialUrl;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\ServiceStoreBuyer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StatisticalController extends BaseController
{
    protected $service;
    protected $favorite;
    protected $delivery;
    protected $buyerServiceReserve;
    protected $numberAccessListServicePage;
    protected $numberAccessServiceDetailPage;
    protected $serviceReview;
    protected $serviceStoreBuyer;
    protected $numberClickOfficialUrl;


    public function __construct(
        Service $service,
        Favorite $favorite,
        Delivery $delivery,
        BuyerServiceReserve $buyerServiceReserve,
        NumberAccessServiceDetailPage $numberAccessServiceDetailPage,
        NumberAccessListServicePage $numberAccessListServicePage,
        ServiceReview $serviceReview,
        ServiceStoreBuyer $serviceStoreBuyer,
        NumberClickOfficialUrl $numberClickOfficialUrl
    ) {
        $this->service = $service;
        $this->favorite = $favorite;
        $this->delivery = $delivery;
        $this->buyerServiceReserve = $buyerServiceReserve;
        $this->numberAccessListServicePage = $numberAccessListServicePage;
        $this->numberAccessServiceDetailPage = $numberAccessServiceDetailPage;
        $this->serviceReview = $serviceReview;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->numberClickOfficialUrl = $numberClickOfficialUrl;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        try {
            $count_favorite = $this->favorite->countByServiceId(1);
            return $this->sendSuccessResponse($count_favorite);
        } catch (Exception $e) {
            $this->log("statistical_index", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Display invoice of buyer by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/statistical-month/{hash_id}",
     *     summary="Seller Service Statistical Current Month",
     *     tags={"Service Statistical"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function serviceStatisticalMonth($hash_id)
    {
        try {
            $data = [];
            $service = $this->service->findByHashId($hash_id);
            if (!$service) return $this->sendSuccessResponse(__('app.not_exist', ['attribute' => __('app.service')]));
            $service_id = $service->id;

            $estimated_delivery = $this->delivery->countEstimatedDeliveryByService($service_id);
            $service_reserve = $this->buyerServiceReserve->countReserveByService($service_id);


            $statistical_access_list_service_page = null;
            $statistical_access_service_detail_page = null;
            $statistical_favorite = null;
            $statistical_review = null;
            $statistical_rating = null;
            $statistical_contract = null;
            $statistical_revenue = null;
            $statistical_click_official_url = null;


            $statistical_access_list_service_page = $this->numberAccessListServicePage->statisticalByServiceMonth($service->id);
            $statistical_access_service_detail_page = $this->numberAccessServiceDetailPage->statisticalByServiceMonth($service->id);
            $statistical_favorite = $this->favorite->countByServiceIdMonth($service->id);
            $statistical_review = $this->serviceReview->countByServiceIdMonth($service->id);
            $statistical_rating = $this->serviceReview->countRantingByServiceIdMonth($service->id);
            $statistical_contract = $this->serviceStoreBuyer->countByServiceIdMonth($service->id);
            $statistical_click_official_url = $this->numberClickOfficialUrl->statisticalByServiceMonth($service_id);
            $statistical_revenue = $this->serviceStoreBuyer->getRevenueOfServiceInMonth($service_id);

            $data['service_reserve'] = $service_reserve;
            $data['service_estimated_delivery'] = $estimated_delivery;
            $data['statistical_access_list_service_page'] = $statistical_access_list_service_page;
            $data['statistical_access_service_detail_page'] = $statistical_access_service_detail_page;
            $data['statistical_favorite'] = $statistical_favorite;
            $data['statistical_review'] = $statistical_review;
            $data['statistical_rating'] = $statistical_rating;
            $data['statistical_contracts'] = $statistical_contract;
            $data['statistical_revenue'] = $statistical_revenue;
            $data['statistical_click_official_url'] = $statistical_click_official_url;

            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            $this->log("serviceStatisticalMonth", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/statistical-graph/{hash_id}",
     *     summary="Graph of service in one year",
     *     tags={"Service Statistical"},
     *     security={ {"bearer": {}} },
     *     description = "graph_type: 1:閲覧数（一覧ページ）2:閲覧数（詳細ページ）3:お気に入り登録数 4:口コミ数 
     *                              5:サービス評価数 6:契約数 7:売上高 8:公式サイトURLクリック数 ",
     *     @OA\Parameter(
     *         description="Hash ID of service need to input info",
     *         in="path",
     *         name="hash_id",
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                property="graph_type",
     *                example=1,
     *                type="integer",
     *              ),
     *             @OA\Property(
     *                property="last_month_number",
     *                example=1,
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function graph($hash_id, Request $request)
    {
        try {
            if (!isset($request->graph_type)) {
                $graph_type = 1;
            } else {
                $graph_type = $request->graph_type;
            }

            $user_id = Auth::guard(UserConst::USER_GUARD)->user()->id;
            
            $service = $this->service->findByHashId($hash_id);

            if (!$service) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]));

            if ($user_id != $service->seller_id) return $this->sendError(__('app.not_have_permission'));

            $service_id = $service->id;

            if ($graph_type == 1) {
                $data =  $this->numberAccessListServicePage->graphByService($service_id, $request);
            } elseif ($graph_type == 2) {
                $data = $this->numberAccessServiceDetailPage->graphByService($service_id, $request);
            } elseif ($graph_type == 3) {
                $data =  $this->favorite->graphByService($service_id, $request);
            } elseif ($graph_type == 4) {
                $data = $this->serviceReview->graphByReviewService($service_id, $request);
            } elseif ($graph_type == 5) {
                $data = $this->serviceReview->graphRatingByService($service_id, $request);
            } elseif ($graph_type == 6) {
                $data = $this->serviceStoreBuyer->graphByService($service_id, $request);
            } elseif ($graph_type == 7) {
                $data = $this->serviceStoreBuyer->graphRevenue($service_id, $request);
            } elseif ($graph_type == 8) {
                $data = $this->numberClickOfficialUrl->graphByService($service_id, $request);
            } else {
                $data =  $this->numberAccessListServicePage->graphByService($service_id, $request);
            }

            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            $this->log("graph", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Display invoice of buyer by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/seller/statistical-year/{seller_id}",
     *     summary="Seller Service Statistical Current Year",
     *     tags={"Service Statistical"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Seller ID has service need to input info",
     *         in="path",
     *         name="seller_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *       @OA\Parameter(
     *         description="",
     *         in="query",
     *         name="page",
     *         required=false,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                property="last_year_number",
     *                example=1,
     *                type="integer",
     *              ),
     *              @OA\Property(
     *                property="per_page",
     *                example=1,
     *                type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function serviceStatisticalYear($seller_id, Request $request)
    {
        try {

            $data = $this->service->getAllServiceBySeller($seller_id, $request);

            if (sizeof($data) > 0) {
                foreach ($data as $service) {
                    $service_id = $service->id;

                    $statistical_access_list_service_page = null;
                    $statistical_access_service_detail_page = null;
                    $statistical_favorite = null;
                    $statistical_review = null;
                    $statistical_rating = null;
                    $statistical_contract = null;
                    $statistical_revenue = null;
                    $statistical_click_official_url = null;


                    $statistical_access_list_service_page = $this->numberAccessListServicePage->statisticalByServiceYear($service->id, $request);
                    $statistical_access_service_detail_page = $this->numberAccessServiceDetailPage->statisticalByServiceYear($service_id, $request);
                    $statistical_favorite = $this->favorite->countByServiceIdYear($service->id, $request);
                    $statistical_review = $this->serviceReview->countByServiceIdYear($service->id, $request);
                    $statistical_rating = $this->serviceReview->countRantingByServiceIdYear($service->id, $request);
                    $statistical_contract = $this->serviceStoreBuyer->countByServiceIdYear($service->id, $request);
                    $statistical_revenue = $this->serviceStoreBuyer->revenueOfServiceByYear($service_id, $request);
                    $statistical_click_official_url = $this->numberClickOfficialUrl->statisticalByServiceYear($service_id, $request);


                    $service->statistical_access_list_service_page = $statistical_access_list_service_page;
                    $service->statistical_access_service_detail_page = $statistical_access_service_detail_page;
                    $service->statistical_favorite = $statistical_favorite;
                    $service->statistical_review = $statistical_review;
                    $service->statistical_rating = $statistical_rating;
                    $service->statistical_contracts = $statistical_contract;
                    $service->statistical_revenue = $statistical_revenue;
                    $service->statistical_click_official_url = $statistical_click_official_url;
                    
                    unset($service->id);
                }
            }
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            $this->log("serviceStatisticalYear", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display invoice of buyer by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/statistical/list-service/{seller_id}",
     *     summary="List service of seller",
     *     tags={"Service Statistical"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Seller's ID has service need to input info",
     *         in="path",
     *         name="seller_id",
     *         required=true,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getAllServiceBySellerId($seller_id)
    {
        try {
            $data = $this->service->getAllServiceSellingBySellerId($seller_id);
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            $this->log("getAllServiceBySellerId", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
