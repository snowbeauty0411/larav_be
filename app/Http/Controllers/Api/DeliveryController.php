<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Account;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Delivery;
use App\Models\Payment;
use App\Constants\UserConst;
use App\Models\ActionPayment;
use App\Models\Service;
use App\Models\ServiceStoreBuyer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class DeliveryController extends BaseController
{
    protected $delivery;
    protected $account;
    protected $buyer;
    protected $payment;
    protected $services;
    protected $serviceStoreBuyer;

    public function __construct(
        Delivery $delivery,
        Account $account,
        Buyer $buyer,
        Payment $payment,
        Service $services,
        ServiceStoreBuyer $serviceStoreBuyer,
        ActionPayment $actionPayment
    ) {
        $this->delivery = $delivery;
        $this->account = $account;
        $this->buyer = $buyer;
        $this->payment = $payment;
        $this->services = $services;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->actionPayment = $actionPayment;
    }

    public function filterRules()
    {
        return [
            'course_id' => 'nullable|string',
            'payment_status' => 'nullable|integer',
            'delivery_status' => 'nullable|integer',
            'start_date_month' => 'nullable|string',
            'end_date_month' => 'nullable|string'
        ];
    }

    public function customMessage()
    {
        return [
            'course_id.string' => __('validation.string', ['attribute' => __('app.course_id')]),
            'payment_status.integer' => __('validation.integer', ['attribute' => __('app.payment_status')]),
            'delivery_status.integer' => __('validation.integer', ['attribute' => __('app.delivery_status')]),
            'delivery_status.required' => __('validation.required', ['attribute' => __('app.delivery_status')]),
            'delivery_date.string' => __('validation.integer', ['attribute' => __('app.delivery_date')]),
            'delivery_date.required' => __('validation.required', ['attribute' => __('app.delivery_date')]),
        ];
    }

    public function updateDeliveryStatusRules()
    {
        return [
            'delivery_status' => 'required|integer|min:0|max:2',
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/service/{hash_id}/delivery/list",
     *     summary="list delivery service seller",
     *     tags={"Deliveries"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash ID service need display",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *      @OA\Parameter(
     *         description="Number record  need display",
     *         in="path",
     *         name="page",
     *         required=false,
     *         example=10,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",                   
     *                   @OA\Property(
     *                    property="delivery_status",
     *                    type="integer",
     *                    example=2
     *             ),
     *                   @OA\Property(
     *                    property="payment_status",
     *                    type="integer",
     *                    example=2
     *             ),
     *                    @OA\Property(
     *                    property="course_id",
     *                    type="string",
     *                    example="A1"
     *             ),
     *                    @OA\Property(
     *                    property="per_page",
     *                    type="integer",
     *                    example="10"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get all deliveries of seller successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function listDeliveryByServiceId($hash_id, Request $request)
    {

        try {
            $credentials = $request->all();
            //valid credential
            $validator = Validator::make($credentials, $this->filterRules(), $this->customMessage());

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), Response::HTTP_OK);
            }

            $service = $this->services->where('hash_id', $hash_id)->first();

            $serviceDelivery = $service->serviceDelivery;

            if (empty($service)) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_NOT_FOUND);
            }
            $deliveryList = $this->delivery->getDeliveryByService($service->id, $request);
            foreach ($deliveryList as $delivery) {
                if ($delivery->profile_image_url_buy != null) {
                    $delivery->profile_image_url_buy = config('app.app_resource_path') . 'avatar/' . $delivery->profile_image_url_buy;
                }
            }
            // if (count($deliveryList) > 0) {
            //     foreach ($deliveryList as $key => $delivery) {
            //         $account_info = $this->account->accountInfo($delivery->buyer_id);
            //         $delivery->account_name = $account_info->buyers ? $account_info->buyers->account_name : "";
            //     }
            // }
            $courseList = array();
            $course = array();
            $list = $this->delivery->getDeliveryByService2($service->id);
            $index = 0;
            if (count($list) > 0) {
                foreach ($list as $delivery) {
                    if (!in_array($delivery->course_id, $course)) {
                        $courseList[$index] = [
                            'value' => $delivery->course_id,
                            'text' => $delivery->course_name
                        ];
                        array_push($course, $delivery->course_id);
                        $index++;
                    }
                }
            }
            $data['deliveryList'] = $deliveryList;
            $data['courseList'] = $courseList;
            $data['serviceDelivery'] = $serviceDelivery;
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            $this->log("listDeliveryByServiceId", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/seller/delivery/{id}/delivery-status",
     *     summary="update delivery status",
     *     tags={"Deliveries"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="ID need update",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *             required={"delivery_status"},
     *              @OA\Property(
     *                  property="delivery_status",
     *                  example="2",
     *                  type="integer",
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Update successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function updateDeliveryStatus($id, Request $request)
    {
        try {
            $data = $request->all();

            $validator = Validator::make($data, $this->updateDeliveryStatusRules());
            $errors = $validator->errors();

            if ( $validator->fails()) return $this->sendError($errors->first(), Response::HTTP_OK);

            $delivery = $this->delivery->where('id', $id)->first();

            if (!$delivery) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.delivery')]));
            }

            if ($delivery->delivery_status == 1 && $request->delivery_status == 2) {
                $this->delivery->where('id', $id)->update([
                    "delivery_status" => 2,
                    "actual_date" => now()
                ]);

                $order = $this->serviceStoreBuyer->where('id', $delivery->service_store_buyer_id)->first();
                $buyer_info = $this->account->where('id', $order->buyer_id)->with('buyers')->first();
                if ($buyer_info->transaction_mail_flg) {
                    $email_buyer = $buyer_info->email;
                    $title = __('app.title_mail_change_delivery_status');
    
                    $data = array(
                        'account_name' => $buyer_info->buyers ? $buyer_info->buyers->account_name : "",
                        'APP_URL' => config('app.url'),
                        'delivery_date' => now()
                    );
    
                    $this->sendEmail('email.email-change-delivery-status', $email_buyer, $data, $title);
                }
            } elseif ($delivery->delivery_status == 1 && $request->delivery_status == 0) {
                $this->delivery->where('id', $id)->update([
                    "delivery_status" => $request->delivery_status,
                ]);

                $actionPayment = $this->actionPayment->getByStoreBuyerId($delivery->service_store_buyer_id);
                
                if ($actionPayment && Carbon::parse($actionPayment['charge_at'])->format('Y-m-d') == Carbon::parse($delivery['estimated_date'])->format('Y-m-d')) {
                    $actionPayment['skip'] = true;
                    $actionPayment->save();
                }

            } elseif($delivery->delivery_status == 0) {
                return $this->sendError(__('app.status_cannot_update'));
            }
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.delivery')]));
        } catch (Exception $e) {
            $this->log("updateDeliveryStatus", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *     @OA\Post(
     *     path="/api/buyer/service/{hash_id}/delivery/list",
     *     summary="list delivery service seller",
     *     tags={"Deliveries"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="Hash ID service",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *      @OA\Parameter(
     *         description="Number record  need display",
     *         in="path",
     *         name="page",
     *         required=false,
     *         example=10,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",                   
     *                   @OA\Property(
     *                    property="delivery_status",
     *                    type="integer",
     *                    example=2
     *             ),
     *                   @OA\Property(
     *                    property="payment_status",
     *                    type="integer",
     *                    example=2
     *             ),
     *                    @OA\Property(
     *                    property="per_page",
     *                    type="integer",
     *                    example="10"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get all deliveries of buyer successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function listDeliveryBuyer($hash_id, Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;
            $credentials = $request->all();
            //valid credential
            $validator = Validator::make($credentials, $this->filterRules(), $this->customMessage());

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), Response::HTTP_OK);
            }
            $service = $this->services->where('hash_id', $hash_id)->first();
            if (empty($service)) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_OK);
            }
            $deliveryList = $this->delivery->getAllByUserIdAndServiceId($id, $service->id, $request);
            // if (count($deliveryList)) {
            //     foreach ($deliveryList as $delivery) {
            //         $account_info = $this->account->accountInfo($delivery->buyer_id);
            //         $delivery->account_name = $account_info->buyers ? $account_info->buyers->account_name : "";
            //     }
            // }
            // $courseList = array();
            // $course = array();
            // $list = $this->delivery->getDeliveryByUserIdAndServiceId($id, $service->id, $request);
            // $index = 0;
            // if (count($list) > 0) {
            //     foreach ($list as $delivery) {
            //         if (!in_array($delivery->course_id, $course)) {
            //             $courseList[$index] = [
            //                 'value' => $delivery->course_id,
            //                 'text' => $delivery->course_name
            //             ];
            //             array_push($course, $delivery->course_id);
            //             $index++;
            //         }
            //     }
            // }
            // $data['deliveryList'] = $deliveryList;
            // $data['courseList'] = $courseList;
            return $this->sendSuccessResponse($deliveryList);
        } catch (Exception $e) {
            $this->log("listDeliveryBuyer", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
