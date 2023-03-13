<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Payment;
use App\Models\ServiceStoreBuyer;
use App\Models\ServiceCourse;
use App\Models\Service;
use App\Constants\UserConst;
use App\Models\Seller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    protected $payment;
    protected $service_store_buyer;
    protected $service;
    protected $serviceCourse;
    protected $seller;

    public function __construct(
        Payment $payment,
        ServiceStoreBuyer $service_store_buyer,
        Service $service,
        ServiceCourse $serviceCourse,
        Seller $seller
    ) {
        $this->payment = $payment;

        $this->service_store_buyer = $service_store_buyer;
        $this->service = $service;
        $this->serviceCourse = $serviceCourse;
        $this->seller = $seller;
    }

    public function filterRules()
    {
        return [
            'course_id' => 'nullable|string',
            'month' => 'nullable|integer'
        ];
    }

    public function customMessage()
    {
        return [
            'course_id.string' => __('validation.string', ['attribute' => __('app.course_id')]),
            'month.string' => __('validation.integer', ['attribute' => __('app.month')]),
        ];
    }


    /**
     *     @OA\Post(
     *     path="/api/buyer/service/{hash_id}/payment/list",
     *     summary="list payment service buyer",
     *     tags={"Payment"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="hash ID service",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *         @OA\Parameter(
     *         description="Number record display",
     *         in="path",
     *         name="page",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get all payments of buyer successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function listPaymentBuyer($hash_id, Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;

            $service = $this->service->where('hash_id', $hash_id)->first();
            if (!$service) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]));
            }
            $service_id = $service->id;
            // $service_store_buyer = $this->service_store_buyer->findByServiceAndUser($service_id, $id);
            // if (!$service_store_buyer) {
            //     return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            // }
            // $service_store_buyer_id = $service_store_buyer->id;
            $data = $this->payment->getAllPaymentBuyer($service_id, $id, $request);
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            error_log($e);
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *     @OA\Get(
     *     path="/api/buyer/payment/detail/{id}",
     *     summary="detail payment of buyer",
     *     tags={"Payment"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID payment",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get detail payments of buyer successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function detailPaymentBuyer($id)
    {
        try {
            $user_id = auth(UserConst::USER_GUARD)->user()->id;

            $payment = $this->payment->findById($id);
            if (!$payment) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.payment')]));
            }
            return $this->sendSuccessResponse($payment);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/seller/{seller_id}/transfer",
     *     summary="get card by seller_id",
     *     tags={"Transfer History"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="seller_id",
     *          required=true,
     *          in="path",
     *          example=1,
     *          @OA\Schema(
     *              type="integer"
     *          )
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function transferApplicationSeller($id)
    {
        try {
            $seller = $this->seller->findByAccountId($id);

            if (!isset($seller))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller')]));

            $user = Auth::guard('users')->user();
            if ($seller->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $results = [];
            $sellerCardInfo = $seller->sellerCardInfo;
            $amount_current = $this->payment->getAmountCurrentBySeller($id);
            $results['amount_current'] = $amount_current;
            $results['identity_verification_status'] = $seller->account->identity_verification_status;
            $results['seller_card'] = $sellerCardInfo;

            return $this->sendSuccessResponse($results);
            return $this->sendSuccessResponse();

        } catch (Exception $e) {
            $this->log("TransferApplicationSeller", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
