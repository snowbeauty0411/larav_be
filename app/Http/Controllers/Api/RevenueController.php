<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Account;
use App\Models\Payment;
use App\Models\ServiceCourse;
use App\Models\Service;
use App\Models\ServiceStoreBuyer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class RevenueController extends BaseController
{
    protected $service_store_buyer;
    protected $payment;
    protected $account;
    protected $service_course;
    protected $service;

    public function __construct(
        ServiceStoreBuyer $service_store_buyer,
        Payment $payment,
        Account $account,
        ServiceCourse $service_course,
        Service $service
    ) {
        $this->service_store_buyer = $service_store_buyer;
        $this->payment = $payment;
        $this->account = $account;
        $this->service_course = $service_course;
        $this->service = $service;
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/revenue/{hash_id}",
     *     summary="List revenue in month of service",
     *     tags={"Revenue"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="hash ID service need display",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *      @OA\Parameter(
     *         description="number record display",
     *         in="path",
     *         name="page",
     *         required=false,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",                   
     *                   @OA\Property(
     *                    property="month",
     *                    type="integer",
     *                    example=2
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get all revenue of service successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function revenueBySeller($hash_id, Request $request)
    {
        try {
            $service = $this->service->where('hash_id', $hash_id)->first();
            $service_id = $service->id;
            $total_revenue = 0;
            $total_paid_all = 0;
            $unpaid_total = 0;
            $revenue_data = $this->payment->getAllPaymentByService($service_id, $request);
            if (count($revenue_data) > 0) {
                foreach ($revenue_data as $revenue) {
                    $total_revenue = $total_revenue + ($revenue->sub_total > 0 ? $revenue->sub_total - ($revenue->service_fee * 0.5) : $revenue->sub_total);

                    if ($revenue->payment_status == 1) {
                        $total_paid_all = $total_paid_all + ($revenue->sub_total > 0 ? $revenue->sub_total - ($revenue->service_fee * 0.5) : $revenue->sub_total);
                    }
                    // else {
                    //     $unpaid_total = $unpaid_total + ($revenue->total > 0 ? $revenue->total - ($revenue->service_fee * 0.5) : $revenue->total);
                    // }

                    if($revenue->profile_image_url_buy != null){
                        $revenue->profile_image_url_buy=config('app.app_resource_path') . 'avatar/' . $revenue->profile_image_url_buy;
                    }
                }
            }
            $curr_year = Carbon::now()->format('Y');
            if (isset($request->month)) {
                $time_query = $curr_year . '年' . $request->month . '月の売上';
            } else {
                $current_month_name = Carbon::now()->format('M');
                $curr_month = date("n", strtotime($current_month_name));
                $time_query = $curr_year . '年' . $curr_month . '月の売上';
            }
            $data = [];
            $data['time_query'] = $time_query;
            $data['total'] = $total_revenue;
            $data['total_paid_all'] = $total_paid_all;
            $data['unpaid_total'] = $unpaid_total;
            $data['revenue'] = $revenue_data;
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
