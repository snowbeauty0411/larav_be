<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Buyer;
use App\Models\BuyerServiceReserve;
use App\Models\ServiceStoreBuyer;
use App\Models\Service;
use App\Models\ServiceCourse;
use App\Models\ShippingInfo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BuyerServiceReserveController extends BaseController
{
    protected $buyerServiceReserve;
    protected $buyer;
    protected $service;
    protected $account;
    protected $serviceCourse;
    protected $shippingInfo;

    public function __construct(
        BuyerServiceReserve $buyerServiceReserve,
        Buyer $buyer,
        Service $service,
        ServiceStoreBuyer $serviceStoreBuyer,
        Account $account,
        ServiceCourse $serviceCourse,
        ShippingInfo $shippingInfo
        )
    {
        $this->buyerServiceReserve = $buyerServiceReserve;
        $this->buyer = $buyer;
        $this->service = $service;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->account = $account;
        $this->serviceCourse = $serviceCourse;
        $this->shippingInfo = $shippingInfo;
    }

    public function serviceReserveRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'service_id' => 'required|integer|exists:services,id',
            'reserve_start' => 'required|date_format:"Y-m-d G:i"',
            'reserve_end' => 'required|date_format:"Y-m-d G:i"',
        ];
    }

    public function deleteServiceReserveRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'service_id' => 'required|integer|exists:services,id',
            'course_id' => 'required|string|exists:service_courses,course_id',
            'reserve_start' => 'required|date_format:"Y-m-d G:i"',
        ];
    }

    public function validator($data, $rules)
    {
        $validator = Validator::make($data, $rules);
        $errors = $validator->errors();
        return $errors->first();
    }
     /**
     * Display the listing review of service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/buyer/{id}/statistic-reservation",
     *     summary="Statistic reservation of buyer",
     *     tags={"Reservations Buyer"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of buyer need to input info",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function statisticReservationByBuyer($buyer_id)
    {
        try {
            $user = Auth::guard('users')->user();

            $buyer = $this->buyer->findByAccountId($buyer_id);
            // check user login with buyer_id sent up
            if ($user->id != $buyer->account_id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            return $this->sendSuccessResponse($this->buyerServiceReserve->statisticReservationByBuyer($buyer_id));
        } catch (Exception $e) {
            $this->log("statisticReservationByBuyer", null, ["buyer_id" =>$buyer_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the listing review of service.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/service/{hash_id}/reservations/list-course",
     *     summary="Get All Course of reservations by Seller",
     *     tags={"Reservations Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of buyer need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="hash_id",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getCourseByServiceId($service_id)
    {
        try {
            $user = Auth::guard('users')->user();

            $service = $this->service->findHashId($service_id);

            if (!$service)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]), Response::HTTP_UNAUTHORIZED);

            if ( $user->id != $service->seller_id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_OK);

            return $this->sendSuccessResponse($this->buyerServiceReserve->getCourseByServiceId($service->id));
        } catch (Exception $e) {
            $this->log("getCourseByServiceId", null, ["service_id" =>$service_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Display the listing reservations of buyer.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/buyer/service/{hash_id}/reservations/list",
     *     summary="Get All reservations of buyer",
     *     tags={"Reservations Buyer"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="HashID of buyer need to input info",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="hash_id",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="buyer_id need to input info",
     *         in="query",
     *         name="buyer_id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="per_page need to input info",
     *         in="query",
     *         name="per_page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         description="page need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getAllByBuyer($service_id, Request $request)
    {
        try {
            $user = Auth::guard('users')->user();

            $service = $this->service->findHashId($service_id);

            if (!$service)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service')]));

            $user = Auth::guard('users')->user();
            $buyer = $this->buyer->findByAccountId($request->buyer_id);

            if($user->id != $buyer->account_id)
                return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $serviceStoreBuyer = $this->serviceStoreBuyer->findServiceUseByBuyer($service->id, $buyer->account_id);
            if (!isset($serviceStoreBuyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_store_buyer')]));

            $service_course = $serviceStoreBuyer->serviceCourses;
            if (!isset($service_course))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.course')]));

            return $this->sendSuccessResponse($this->buyerServiceReserve->getAllByBuyerAndCourseId($buyer->account_id, $service_course->course_id, $request->per_page));

        } catch (Exception $e) {
            $this->log("getReservationsAllByBuyer", null, ["service_id" =>$service_id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/buyer/reservations/create",
     *     summary="Store a newly created reservations",
     *     tags={"Reservations Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Buyer ID need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="2",
     *                 ),
     *                 @OA\Property(
     *                     description="Service ID need to display",
     *                     property="service_id",
     *                     type="integer",
     *                     example="service_id",
     *                 ),
     *                 @OA\Property(
     *                     description="Course ID need to display",
     *                     property="course_id",
     *                     type="string",
     *                     example="course_id",
     *                 ),
     *                  @OA\Property(
     *                     description="reserve_start need to display",
     *                     property="reserve_start",
     *                     type="string",
     *                     example="reserve_start",
     *                 ),
     *                  @OA\Property(
     *                     description="reserve_end need to display",
     *                     property="reserve_end",
     *                     type="string",
     *                     example="reserve_end",
     *                 ),
     *                 required={"buyer_id", "service_id", "course_id", "reserve_start", "reserve_end"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function store(Request $request)
    {
        try {
            // validate
            $data = $request->all();
            $errors = $this->validator($data, $this->serviceReserveRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $buyerServiceReserved = $this->buyerServiceReserve->findByBuyerAndReservesStart($request);
            if ($buyerServiceReserved)
                return $this->sendError(__('app.exist', ['attribute' => __('app.buyer_service_reserve')]));
            $service = $this->service->find($request->service_id);

            $service_reserves_setting =  $service->serviceReserveSetting;
            $data_service_reserve_setting = [
                'duration_after' => $service_reserves_setting->duration_after,
                'duration_before' => $service_reserves_setting->duration_before,
                'max' => $service_reserves_setting->max,
                'time_distance' => $service_reserves_setting->time_distance,
                'type_duration_after' => $service_reserves_setting->type_duration_after,
            ];
    
            $serviceHours = $service->serviceHours->toArray();
            $el_first = array_shift($serviceHours);
            array_push($serviceHours, $el_first);

            $is_reserves = $service->is_reserves;

            $hoursTemp = $service->ServiceHoursTemps;

            $req_reserve_setting = $request->service_reserve_setting;
            $req_service_hours = $request->service_hours;
            $req_is_reserves = $request->is_reserves;
            $req_hours_temp = $request->hours_temp;

            if (count(array_diff($req_reserve_setting, $data_service_reserve_setting)) > 0) {
                return $this->sendError(__('app.updated_system'));
            }

            if (count($req_service_hours) == count($serviceHours)) {
                foreach ($req_service_hours as $key1 => $value1) {
                    foreach ($serviceHours as $key2 => $value2) {
                        if ($key1 == $key2 && count(array_diff($value1, $value2)) > 0) {
                            return $this->sendError(__('app.updated_system'));
                            break;
                        }
                    }
                }
            } else {
                return $this->sendError(__('app.updated_system'));
            }

            if ($req_is_reserves != $is_reserves) {
                return $this->sendError(__('app.updated_system'));
            }

            if (count($req_hours_temp) == count($hoursTemp)) {
                foreach ($req_hours_temp as $key1 => $value1) {
                    foreach ($hoursTemp as $key2 => $value2) {
                        if ($key1 == $key2 && count(array_diff($value1, $value2)) > 0) {
                            return $this->sendError(__('app.updated_system'));
                            break;
                        }
                    }
                }
            } else {
                return $this->sendError(__('app.updated_system'));
            }

            $duration_before = $service_reserves_setting['duration_before'];
            $duration_after = $service_reserves_setting['duration_after'];
            $type_duration_after = $service_reserves_setting['type_duration_after'];
            $date_now = Carbon::parse(Carbon::now()->format('Y-m-d') . '23:59:59');
            $time_now = Carbon::now()->toDateTimeString();
            $date_start = Carbon::createFromFormat('Y-m-d G:i', $request->reserve_start);
            $date_end = Carbon::createFromFormat('Y-m-d G:i', $request->reserve_end);

            $part_distances = explode(':', $service_reserves_setting['time_distance']);

            $hours_distance = (int) $part_distances[0];
            $minutes_distance = (int) $part_distances[1];

            $hours_start = $date_start->format('G');
            $minutes_start = $date_start->format('i');
            $hours_end = $date_end->format('G');
            $minutes_end = $date_end->format('i');

            $hours_end_check = $hours_start + $hours_distance;
            $minutes_end_check = $minutes_start + $minutes_distance;
            if ($minutes_end_check >= 60) {
                $minutes_end_check = $minutes_end_check - 60;
                $hours_end_check++;
            }

            if ((int) $hours_end_check != $hours_end || (int) $minutes_end_check != $minutes_end || $date_start->format('Y-m-d') != $date_end->format('Y-m-d'))
                return $this->sendError(__('app.invalid', ['attribute' => __('app.reserve_time')]));

            if ($date_start->diffInDays($date_now) + 1 > $duration_before)
                return $this->sendError(__('app.reserves_duration_before', ['date' => $duration_before]));
            
            if ($type_duration_after == 1 && $date_start->diffInDays($date_now) < $duration_after) 
                return $this->sendError(__('app.reserves_duration_after', ['date' => $duration_after]));

            if ($type_duration_after == 2 && $date_start->diffInHours($time_now) < $duration_after)
                return $this->sendError(__('app.reserves_duration_after_frame', ['date' => $duration_after]));

            $buyerServiceReserves = $this->buyerServiceReserve->findByReservesStartAndCourseId($request);
            if (isset($service_reserves_setting['max']) && $service_reserves_setting['max'] == count($buyerServiceReserves))
                return $this->sendError(__('app.reached_maximum_reserves'));

            $buyerServiceReserve = $this->buyerServiceReserve->create($data);

            if ($buyerServiceReserve) {

                $seller_id = $service->seller_id;
                $account = $this->account->find($seller_id);
                $seller = $account->sellers;

                $buyer_id = $request->buyer_id;
                $buyer = $this->buyer->where('account_id', $buyer_id)->first();
                $shipping_info = $this->shippingInfo->findDefaultByBuyerId($buyer_id);

                $course_id = $request->course_id;
                $course = $this->serviceCourse->where('course_id', $course_id)->first();
                
                if ($account['transaction_mail_flg']) {
                    $title = '【subsQ】予約のお知らせ';
                    $data = [
                        'seller_name'       => $seller['account_name'],
                        'service_name'      => $service['name'],
                        'course_id'         => $course['course_id'],
                        'course_name'       => $course['name'],
                        'price'             => $course['price'],
                        'registrant_name'   => $buyer['account_name'],
                        'registered_date'   => Carbon::now()->format('Y-m-d H:i'),
                        'registrant_address'=> $shipping_info ? $shipping_info['post_code'] . $shipping_info['address'] : '',
                    ];
                    $this->sendEmail('email.email-notify-appointment', $account['email'], $data, $title);
                }
                return $this->sendSuccess(__('app.buyer_reserves_create'));
            } else {
                return $this->sendSuccess(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.buyer_service_reserve')]));
            }
        } catch (Exception $e) {
            $this->log("createReservations", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Remove the specified resource from storage.
     *  @OA\Post(
     *     path="/api/buyer/reservations/delete",
     *     summary="delete comment by id",
     *     tags={"Reservations Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Buyer ID need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="2",
     *                 ),
     *                 @OA\Property(
     *                     description="ServiceID need to display",
     *                     property="service_id",
     *                     type="integer",
     *                     example="service_id",
     *                 ),
     *                 @OA\Property(
     *                     description="Course ID need to display",
     *                     property="course_id",
     *                     type="string",
     *                     example="course_id",
     *                 ),
     *                  @OA\Property(
     *                     description="reserve_start need to display",
     *                     property="reserve_start",
     *                     type="string",
     *                     example="reserve_start",
     *                 ),
     *                 required={"buyer_id", "course_id", "reserve_start"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $data = $request->all();

            $errors = $this->validator($data, $this->deleteServiceReserveRules());
            if ($errors) return $this->sendError($errors, Response::HTTP_OK);

            $service = $this->service->find($request->service_id);
            $service_reserves_setting =  $service->serviceReserveSetting;

            $duration_after = $service_reserves_setting['duration_after'];
            $type_duration_after = $service_reserves_setting['type_duration_after'];
            $date_now = Carbon::now();
            $date_start = Carbon::createFromFormat('Y-m-d G:i', $request->reserve_start);
            $time_now = Carbon::now()->toDateTimeString();

            if ($type_duration_after == 1 && $date_start->diffInDays($date_now) < $duration_after)
                return $this->sendError(__('app.reserves_duration_after', ['date' => $duration_after]));

            if ($type_duration_after == 2 && $date_start->diffInHours($time_now) < $duration_after)
                return $this->sendError(__('app.reserves_duration_after_frame', ['date' => $duration_after]));

            $buyerServiceReserve = $this->buyerServiceReserve->findByBuyerAndReservesStart($request);
            if (!$buyerServiceReserve)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer_service_reserve')]));

            $deleted = $this->buyerServiceReserve->deleteByBuyerAndReservesStart($request);

            if ($deleted) {
                return $this->sendSuccess(__('app.buyer_reserves_cancel'));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.buyer_service_reserve')]));
            }
        } catch (Exception $e) {
            $this->log("deleteServiceReserves", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
