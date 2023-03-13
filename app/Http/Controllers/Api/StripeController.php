<?php

namespace App\Http\Controllers\Api;

use App\Constants\ServiceConst;
use App\Models\Account;
use App\Models\ActionDelivery;
use App\Models\ActionPayment;
use App\Models\Buyer;
use App\Models\Payment;
use App\Models\ServiceCourse;
use App\Models\ShippingInfo;
use App\Models\Seller;
use App\Models\ServiceStoreBuyer;
use App\Models\Service;
use App\Models\Delivery;
use App\Models\ServiceDelivery;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeController extends BaseController
{
    protected $stripeClient;
    protected $buyer;
    protected $account;
    protected $seller;
    protected $serviceCourse;
    protected $shippingInfo;
    protected $actionPayment;
    protected $actionDelivery;
    protected $serviceStoreBuyer;
    protected $service;
    protected $delivery;
    protected $serviceDelivery;

    public function __construct(
        StripeClient $stripeClient,
        Buyer $buyer,
        Account $account,
        Seller $seller,
        ServiceCourse $serviceCourse,
        ShippingInfo $shippingInfo,
        ActionPayment $actionPayment,
        ServiceStoreBuyer $serviceStoreBuyer,
        Service $service,
        Delivery $delivery,
        Payment $payment,
        ServiceDelivery $serviceDelivery
    ) {
        $this->stripeClient = $stripeClient;
        $this->buyer = $buyer;
        $this->account = $account;
        $this->seller = $seller;
        $this->serviceCourse = $serviceCourse;
        $this->shippingInfo = $shippingInfo;
        $this->actionPayment = $actionPayment;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->service = $service;
        $this->delivery = $delivery;
        $this->payment = $payment;
        $this->serviceDelivery = $serviceDelivery;
    }

    /**
     * Get the validation rules card that apply to the request.
     *
     * @return array

     */

    public function rulesPayment()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'course_id' => 'required|string|exists:service_courses,course_id',
            'shipping_info_id' => 'integer|exists:shipping_info,id',
            'card_id' => 'required|string',
        ];
    }

    public function rulesCard()
    {
        return [
            'card_no' => 'required|string|max:16',
            'expiry_month' => 'required',
            'expiry_year' => 'required',
            'cvc' => 'required|string',
        ];
    }

    public function rulesCardTest()
    {
        return [
            'buyer_id' => 'required|integer',
            'course_id' => 'required|string',
            'card.card_no' => 'required|string|max:16',
            'card.expiry_month' => 'required',
            'card.expiry_year' => 'required',
            'card.cvc' => 'required|string',
        ];
    }

    public function addCardRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'token_card' => 'required|string',
            'card_name' => 'string',
            'is_default' => 'boolean',
        ];
    }

    public function updateCardRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
            'exp_month' => 'required|integer|min:1|max:12',
            'exp_year' => 'required|integer',
            'card_name' => 'required|string|max:255',
            'is_default' => 'boolean',
        ];
    }

    public function buyerCardRules()
    {
        return [
            'buyer_id' => 'required|integer|exists:buyers,account_id',
        ];
    }

    /**
     * Create the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/stripe-payment",
     *     summary="Stripe payment",
     *     tags={"Stripe Payment"},
     *     @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"buyer_id","course_id", "card_id"},
     *                          @OA\Property(
     *                             property="buyer_id",
     *                             example="2",
     *                             type="integer",
     *                         ),
     *                          @OA\Property(
     *                             property="course_id",
     *                             example="A1",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="card_id",
     *                             example="card_id",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="shipping_info_id",
     *                             example="1",
     *                             type="integer",
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="許可がありません。",
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
            $validator = Validator::make($request->all(), $this->rulesPayment());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            DB::beginTransaction();

            //check service course exists
            $service_course = $this->serviceCourse->findByCourseId($request->course_id);
            if (!isset($service_course))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_course')]));

            // check buyer exists
            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);

            if (!isset($buyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            if (!isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.customer_stripe')]));

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            // check exists buyer used this service
            $check_buyer_used = $this->serviceStoreBuyer->findServiceUseByBuyer($service_course['service_id'], $buyer['account_id']);
            if ($check_buyer_used)
                return $this->sendError(__('app.buyer_used'));

            // check max store of service
            $check_max_store = $this->service->getCurrentQuantityByID($service_course['service_id']);
            if (isset($check_max_store) && $check_max_store <= 0)
                return $this->sendError(__('app.max_stored'));

            $service = $this->service->find($service_course['service_id']);
            if ($service->seller_id == $user->id) return $this->sendError(__('app.not_allowed_buy'));

            // check shipping
            $shipping_info = $this->shippingInfo->find($request->shipping_info_id);
            if ($service->service_type_id == 1) {

                if (!isset($shipping_info)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.shipping_info')]));

                $serviceDelivery = $service->serviceDelivery;
                if (!$serviceDelivery) return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_delivery')]));

                // $interval_delivery = $serviceDelivery->interval ? $serviceDelivery->month_delivery * 30 : 7;
            }

            // Check customer register in Stripe
            try {
                $customer = $this->stripeClient->customers->retrieve(
                    $buyer['stripe_customer_id'],
                    []
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                DB::rollBack();
                $error_code = $e->getError()->code;
                $this->log("updateCard", null, ['error_code' => $e->getError()->code], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
                return $this->sendError(__('app.' . $error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
            }

            $dataSeller = [
                'seller_name' => $service->seller->account_name,
                'service_name' => $service->name,
                'course_id' => $service_course->course_id,
                'course_name' => $service_course->name,
                'price' => $service_course->price,
                'registrant_name' => $buyer->account_name,
                'registered_date' => Carbon::now()->format('Y-m-d'),
                'registrant_address' => $shipping_info ? $shipping_info['post_code'] . $shipping_info['address'] : '',
            ];

            // data create store service buyer
            $store_create = [
                'course_id' => $service_course['course_id'],
                'buyer_id' => $buyer['account_id'],
                'buy_at' => Carbon::now()->toDateTimeString(),
                'status' => 1,
                'start' => Carbon::now()->toDateTimeString(),
            ];

            // create store service buyer
            $serviceStoreBuyer = $this->serviceStoreBuyer->create($store_create);

            // fee information of course
            $price = $service_course['price'];
            $service_fee = ($price * ServiceConst::SERVICE_FEE) / 100;
            $amount = floor($price + $service_fee);

            if ($service->service_type_id == 1) {
                $charge_at = Carbon::now()->toDateTimeString();
                $charge_at_next = $serviceDelivery->interval == 0 ? Carbon::now()->addWeek()->toDateString() : Carbon::now()->addMonths($serviceDelivery->month_delivery)->toDateString();
            } else {
                $charge_at = Carbon::now()->toDateTimeString();
                $charge_at_next = Carbon::now()->addMonths($service_course['cycle'])->toDateTimeString();
            }
            
            $serviceStoreBuyer->update(['end' => $charge_at_next]);

            // Payment processing with course not free first month
            if (!$service_course['firstPr']) {
                try {
                    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                    $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                    // Make payment when start use service
                    $charge = $stripe->charges->create([
                        'currency' => 'JPY',
                        'amount' =>  $amount,
                        'customer' => $customer['id'],
                        'card' => $request->card_id,
                        'description' => $service_course['name'],
                    ]);

                    Log::channel('paymentlogcustom')->info(json_encode([
                        'buyer_id' => $buyer->account_id,
                        'time' => Carbon::now()->format('Y-m-d H:i:s'),
                        'price' => $amount,
                        'store_buyer_id' => $serviceStoreBuyer['id'],
                        'charge_id' => $charge->id,
                        'card_id' => $request->card_id,
                    ]));

                    $payment_first = $this->payment->create([
                        'id' => $this->payment->generatePaymentId(),
                        'sub_total' => $price,
                        'service_fee' => $service_fee,
                        'total' => $amount,
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'card_id' => $request->card_id,
                        'pay_expire_at_date' => $charge_at,
                        'stripe_charge_id' => $charge->id,
                        'payment_status' => 1,
                    ]);

                    $payment_next = $this->payment->create([
                        'id' => $this->payment->generatePaymentId(),
                        'sub_total' => $price,
                        'service_fee' => $service_fee,
                        'total' => $amount,
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'card_id' => $request->card_id,
                        'pay_expire_at_date' => $charge_at_next,
                        'stripe_charge_id' => null,
                        'payment_status' => 0,
                        'created_at' => $charge_at,
                        'updated_at' => $charge_at
                    ]);
                } catch (\Stripe\Exception\CardException $e) {
                    return $e;
                    DB::rollBack();
                    $error_code = $e->getError()->code;
                    $this->log("Payment", null, ['error_code' => $e->getError()->code], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
                    return $this->sendError(__('app.' . $error_code), Response::HTTP_OK);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    DB::rollBack();
                    $error_code = $e->getError()->code;
                    $this->log("Payment", null, ['error_code' => $e->getError()->code], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
                    return $this->sendError(__('app.' . $error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->log("Payment", null, $request->all(), $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
                    return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                if ($service->service_type_id == 1) {
                    // create delivery interval current
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_first->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => $serviceDelivery->interval == 0 ? Carbon::now()->addWeek()->toDateString() : Carbon::now()->addMonths($serviceDelivery->month_delivery)->toDateString(),
                        'delivery_status' => 1,
                    ]);

                    // create delivery interval next
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_next->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => $serviceDelivery->interval == 0 ? Carbon::parse($charge_at_next)->addWeek()->toDateString() : Carbon::parse($charge_at_next)->addMonths($serviceDelivery->month_delivery)->toDateString(),
                        'delivery_status' => 1,
                    ]);

                    // create action payment
                    $this->actionPayment->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'card_id' => $request->card_id,
                        'skip' => 0,
                        'charge_at' => $charge_at,
                    ]);
                } else {
                    // create action payment
                    $this->actionPayment->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'card_id' => $request->card_id,
                        'skip' => 0,
                        'charge_at' => $charge_at,
                    ]);
                }
                DB::commit();

                if ($buyer->account['transaction_mail_flg']) {
                    $title = '【subsQ】お支払いのお知らせ';
                    $data = [
                        'buyer_name' => $buyer['account_name'],
                        'course_id' => $service_course['id'],
                        'course_name' => $service_course['name'],
                    ];
                    $this->sendEmail('email.email-payment-success', $buyer->account['email'], $data, $title);
                }

                $this->sendEmail('email.email-payment-success-seller', $service->seller->account['email'], $dataSeller, 'SubsQよりサービスの購入がありました');
                return $this->sendSuccess(__('app.payment_success'));
            } else {
                // create payment free first month
                $payment_first = $this->payment->create([
                    'id' =>  $this->payment->generatePaymentId(),
                    'sub_total' => 0,
                    'service_fee' => 0,
                    'total' => 0,
                    'service_store_buyer_id' => $serviceStoreBuyer['id'],
                    'card_id' => $request->card_id,
                    'pay_expire_at_date' => $charge_at,
                    'stripe_charge_id' => null,
                    'payment_status' => 1,
                ]);

                $payment_next = $this->payment->create([
                    'id' => $this->payment->generatePaymentId(),
                    'sub_total' => $price,
                    'service_fee' => $service_fee,
                    'total' => $amount,
                    'service_store_buyer_id' => $serviceStoreBuyer['id'],
                    'card_id' => $request->card_id,
                    'pay_expire_at_date' => $charge_at_next,
                    'stripe_charge_id' => null,
                    'payment_status' => 0,
                    'created_at' => $charge_at,
                    'updated_at' => $charge_at,
                ]);

                if ($service->service_type_id == 1 && $serviceDelivery->month_delivery == 0) {
                    // create delivery interval current
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_first->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => Carbon::now()->toDateString(),
                        'delivery_status' => 1,
                    ]);

                    // create delivery interval next
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_next->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => $charge_at_next,
                        'delivery_status' => 1,
                    ]);
                } elseif ($service->service_type_id == 1) {
                    // create delivery interval current
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_first->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => $serviceDelivery->interval == 0 ? Carbon::now()->addWeek()->toDateString() : Carbon::now()->addMonths($serviceDelivery->month_delivery)->toDateString(),
                        'delivery_status' => 1,
                    ]);

                    // create delivery interval next
                    $this->delivery->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'payment_id' => $payment_next->id,
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'estimated_date' => $serviceDelivery->interval == 0 ? Carbon::parse($charge_at_next)->addWeek()->toDateString() : Carbon::parse($charge_at_next)->addMonths($serviceDelivery->month_delivery)->toDateString(),
                        'delivery_status' => 1,
                    ]);
                }

                if ($service->service_type_id == 1) {
                    // create action payment
                    $this->actionPayment->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'buyer_full_name' => $shipping_info['first_name'] . ' ' . $shipping_info['last_name'],
                        'delivery_address' => $shipping_info['post_code'] . $shipping_info['address'],
                        'card_id' => $request->card_id,
                        'skip' => 0,
                        'charge_at' => $charge_at,
                    ]);
                } else {
                    // create action payment
                    $this->actionPayment->create([
                        'service_store_buyer_id' => $serviceStoreBuyer['id'],
                        'card_id' => $request->card_id,
                        'skip' => 0,
                        'charge_at' => $charge_at,
                    ]);
                }


                DB::commit();

                if ($buyer->account['transaction_mail_flg']) {

                    $title = '【subsQ】お支払いのお知らせ';

                    $data = [
                        'buyer_name' => $buyer['account_name'],
                        'course_id' => $service_course['id'],
                        'course_name' => $service_course['name'],
                    ];

                    $this->sendEmail('email.email-payment-skip', $buyer->account['email'], $data, $title);
                }

                $this->sendEmail('email.email-payment-success-seller', $service->seller->account['email'], $dataSeller, 'SubsQよりサービスの購入がありました');
                Log::channel('paymentlogcustom')->info(Carbon::now() . ' Skip charge free first month');
                return $this->sendSuccess(__('app.payment_success'));
            }
        } catch (\Stripe\Exception\CardException $e) {
            DB::rollBack();
            $error_code = $e->getError()->code;
            $this->log("Payment", null, ['error_code' => $e->getError()], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError(__('app.' . $error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            DB::rollBack();
            $error_code = $e->getError()->code;
            $this->log("Payment", null, ['error_code' => $e->getError()->code], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError(__('app.' . $error_code, ['attribute' => __('app.card')]), Response::HTTP_OK);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            DB::rollBack();
            $error_code = $e->getError()->code;
            $this->log("Payment", null, ['error_code' => $e->getError()->code], $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError(__('app.' . $error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            DB::rollBack();
            $this->log("Payment", null, $request->all(), $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError('__(app.network_failed)', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("Payment", null, $request->all(), $e->getFile() . " " . $e->getLine() . " "  . $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/stripe-payment-test",
     *     summary="test stripe payment",
     *     tags={"Stripe Payment"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"buyer_id","course_id"},
     *                          @OA\Property(
     *                             property="buyer_id",
     *                             example="2",
     *                             type="integer",
     *                         ),
     *                          @OA\Property(
     *                             property="course_id",
     *                             example="A1",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="skip",
     *                             example="false",
     *                             type="boolean",
     *                         ),
     *                          @OA\Property(
     *                             property="card",
     *                             type="object",
     *                             @OA\Property(
     *                                  property="card_no",
     *                                  example="4242424242424242",
     *                                  type="string",
     *                              ),
     *                             @OA\Property(
     *                                  property="expiry_month",
     *                                  example="12",
     *                                  type="string",
     *                              ),
     *                             @OA\Property(
     *                                  property="expiry_year",
     *                                  example="2025",
     *                                  type="string",
     *                              ),
     *                             @OA\Property(
     *                                  property="cvc",
     *                                  example="123",
     *                                  type="string",
     *                              ),
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="許可がありません。",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function testPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rulesCardTest());
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }
            $card = $request->card;
            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);
            if (!isset($buyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            $service_course = $this->serviceCourse->findByCourseId($request->course_id);
            if (!isset($service_course))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.service_course')]));
            Stripe::setApiKey(config('services.stripe.secret'));

            //create token card stripe
            $token = $this->stripeClient->tokens->create(array(
                "card" => array(
                    "number"    => $card['card_no'],
                    "exp_month" => $card['expiry_month'],
                    "exp_year"  => $card['expiry_year'],
                    "cvc"       => $card['cvc']
                )
            ));

            if (!isset($buyer['stripe_customer_id'])) {
                //create customer stripe
                $customer = $this->stripeClient->customers->create([
                    'source' => $token['id'],
                    'email' => $buyer['account']['email'],
                    'name' => $buyer['account']['first_name'] . ' ' . $buyer['account']['last_name']
                ]);
                //update stripe_customer_id buyer
                $this->buyer->updateBuyer($request->buyer_id, array('stripe_customer_id' => $customer['id']));
            } else {
                //update card default customer stripe
                $customer = $this->stripeClient->customers->update(
                    $buyer['stripe_customer_id'],
                    ['source' => $token['id']]
                );
            }
            $charge = $this->stripeClient->charges->create([
                'currency' => 'JPY',
                'amount' =>  $service_course['price'],
                'customer' => $customer['id'],
                'description' => $service_course['name'],
            ]);
            $action_payment = $this->actionPayment->findByBuyerIdAndCourseId($request->buyer_id, $service_course['course_id']);
            if (!isset($action_payment)) {
                $new_action_payment = new ActionPayment();
                $new_action_payment['buyer_id'] = $request->buyer_id;
                $new_action_payment['course_id'] = $service_course['course_id'];
                $new_action_payment['skip'] = $request->skip;
                $new_action_payment->save();
            }
            if ($request->skip) {
                Log::channel('paymentlogcustom')->info(Carbon::now() . ' Skip charge');
            } else {
                Log::channel('paymentlogcustom')->info(Carbon::now() . ' Charge success, ' . $service_course['price'] . '円');
            }
            return $this->sendSuccessResponse($charge);
        } catch (Exception $e) {
            $this->log("test_payment", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTokenCard(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rulesCard());
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }
            $card = $request->all();

            try {
                $token = $this->stripeClient->tokens->create(array(
                    "card" => array(
                        "number"    => $card['card_no'],
                        "exp_month" => $card['expiry_month'],
                        "exp_year"  => $card['expiry_year'],
                        "cvc"       => $card['cvc']
                    )
                ));
            return $this->sendSuccessResponse(['token' => $token['id'],'fingerprint' => $token['card']->fingerprint]);
            } catch (Exception $e) {
                return $this->sendError("Card Error!");
            }
        } catch (Exception $e) {
            $this->log("getTokenCard", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/card/create",
     *     summary="Update Card info",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="card_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1"
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="buyer_id",
     *                 ),
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="token_card",
     *                     type="string",
     *                     example="token_card",
     *                 ),
     *                 @OA\Property(
     *                     description="is_default need to display",
     *                     property="is_default",
     *                     type="boolean",
     *                     example="0",
     *                 ),
     *                 required={"token_card","buyer_id"},
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
     *
     */
    public function addCard(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), $this->addCardRules());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $token = $this->stripeClient->tokens->retrieve(
                $request->token_card,
                []
            );

            $card_fingerprint = $token->card->fingerprint;
            // return $this->sendSuccessResponse($card_fingerprint);
            if (!isset($buyer['stripe_customer_id'])) {
                     //create customer stripe
                    $customer = $this->stripeClient->customers->create([
                        'email' => $buyer['account']['email'],
                        'name' => $buyer['account_name']
                    ]);
                //update stripe_customer_id buyer
                $this->buyer->updateBuyer($request->buyer_id, array('stripe_customer_id' => $customer['id']));

            } else {
                    $customer = $this->stripeClient->customers->retrieve(
                        $buyer['stripe_customer_id'],
                        []
                    );
                    if(isset($customer['deleted']) && $customer['deleted']){
                        $customer = $this->stripeClient->customers->create([
                            'email' => $buyer['account']['email'],
                            'name' => $buyer['account_name']
                        ]);
                        //update stripe_customer_id buyer
                        $this->buyer->updateBuyer($request->buyer_id, array('stripe_customer_id' => $customer['id']));
                    }
            }

            // get all card of customer ID
            $listCards = $this->stripeClient->customers->allSources(
                $customer['id'],
                ['object' => 'card']
            );

            $list_fingerprint_exists = array_map(function($item) {
                return $item->fingerprint;
            }, $listCards->data);

            if (in_array($card_fingerprint, $list_fingerprint_exists)) return $this->sendError(__('app.exist', ['attribute' => __('app.card')]));

            $source = $this->stripeClient->customers->createSource(
                $customer['id'],
                ['source' => $request->token_card]
            );

            if ($request->is_default) {
                $customer = $this->stripeClient->customers->update(
                    $customer['id'],
                    [
                        'default_source' => $source['id']
                    ]
                    );
            }

            if($request->card_name){
                $source = $this->stripeClient->customers->updateSource(
                    $customer['id'],
                    $source['id'],
                    ['name' => $request->card_name]
                );
            }
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.card')]));
        } catch(\Stripe\Exception\CardException $e) {
            $error_code = $e->getError()->code;
            $this->log("addCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        }catch (\Stripe\Exception\InvalidRequestException $e) {
            $error_code = $e->getError()->code;
            $this->log("addCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.token_card')]), Response::HTTP_OK);
        } catch(\Stripe\Exception\AuthenticationException $e) {
            $error_code = $e->getError()->code;
            $this->log("addCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("addCard", null, $request->all(), $e->getMessage());
            return $this->sendError('app.network_failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("addCard", null, $request->all(), $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/card/buyer/{buyer_id}",
     *     summary="get all Card by buyer_id",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function getAllCardByBuyer($buyer_id, Request $request)
    {
        try {
            $buyer = $this->buyer->getProfileBuyer($buyer_id);

            if (!isset($buyer) || !isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));

            $user = Auth::guard('users')->user();
                if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $customer = $this->stripeClient->customers->retrieve(
                $buyer['stripe_customer_id'],
                []
            );
            $results = [];
            $listCards = $this->stripeClient->customers->allSources(
                $customer['id'],
                [
                    'object' => 'card',
                ]
            );
            $results['card_default'] = $customer['default_source'];
            $results['cards'] = $listCards->data;
            return $this->sendSuccessResponse($results);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $error_code = $e->getError()->code;
            $this->log("getAllCardByBuyer", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
        } catch(\Stripe\Exception\AuthenticationException $e) {
            $error_code = $e->getError()->code;
            $this->log("getAllCardByBuyer", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("getAllCardByBuyer", null, ['buyer_id' => $buyer_id], $e->getMessage());
            return $this->sendError(__('app.network_failed'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("getAllCardByBuyer", null, ['buyer_id' => $buyer_id], $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/card/edit/{card_id}",
     *     summary="Update Comment",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="card_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1"
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="buyer_id",
     *                 ),
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="card_name",
     *                     type="string",
     *                     example="CARD_NAME",
     *                 ),
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="exp_month",
     *                     type="integer",
     *                     example="12",
     *                 ),
     *                 @OA\Property(
     *                     description="description need to display",
     *                     property="exp_year",
     *                     type="integer",
     *                     example="2025",
     *                 ),
     *                 @OA\Property(
     *                     description="is_default need to display",
     *                     property="is_default",
     *                     type="boolean",
     *                     example="0",
     *                 ),
     *                 required={"buyer_id","card_name","exp_month","exp_year"},
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
     *
     */
    public function updateCard($card_id, Request $request)
    {
        try {

            $validator = Validator::make($request->all(), $this->updateCardRules());
            $errors = $validator->errors();

            if ($errors->first())
                return $this->sendError($errors->first());

            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (!isset($buyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            if (!isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));

            try {
                $customer = $this->stripeClient->customers->retrieve(
                    $buyer['stripe_customer_id'],
                    []
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("updateCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
            }

            try {
                $card = $this->stripeClient->customers->updateSource(
                    $customer['id'],
                    $card_id,
                    [
                        'name' =>  $request->card_name,
                        'exp_month' => $request->exp_month,
                        'exp_year' => $request->exp_year
                    ]
                );

                if ($request->is_default) {
                    $customer = $this->stripeClient->customers->update(
                        $customer['id'],
                        [
                            'default_source' => $card['id']
                        ]
                        );
                }

                return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.card')]));
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("updateCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.card')]), Response::HTTP_OK);
            }
        } catch(\Stripe\Exception\CardException $e) {
            $error_code = $e->getError()->code;
            $this->log("updateCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch(\Stripe\Exception\AuthenticationException $e) {
            $error_code = $e->getError()->code;
            $this->log("updateCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("updateCard", null, $request->all(), $e->getMessage());
            return $this->sendError('app.network_failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("updateCard", null, $request->all(), $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *  Remove the specified resource from storage.
     *  @OA\Delete(
     *     path="/api/card/delete/{card_id}",
     *     summary="delete Card by card_id",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="card_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
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
    public function deleteCard($card_id, Request $request)
    {
        try {

            $validator = Validator::make($request->all(), $this->buyerCardRules());
            $errors = $validator->errors();

            if ($errors->first())
                return $this->sendError($errors->first());

            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (!isset($buyer) || !isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            $action_payment = $this->actionPayment->findByBuyerIdAndCardId($buyer->account_id, $card_id);
            if ($action_payment)
                return $this->sendError(__('app.card_is_use'));
            try {
                $customer = $this->stripeClient->customers->retrieve(
                    $buyer['stripe_customer_id'],
                    []
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("deleteCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
            }

            try {
                $card = $this->stripeClient->customers->deleteSource(
                    $customer['id'],
                    $card_id,
                    []
                );
                if (isset($card->deleted) && $card->deleted == true) {
                    return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.card')]));
                } else {
                    return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.card')]));
                }
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("deleteCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.card')]), Response::HTTP_OK);
            }
        } catch(\Stripe\Exception\CardException $e) {
            $error_code = $e->getError()->code;
            $this->log("deleteCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch(\Stripe\Exception\AuthenticationException $e) {
            $error_code = $e->getError()->code;
            $this->log("deleteCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
            return $this->sendError(__('app.'.$error_code), Response::HTTP_OK);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("deleteCard", null, $request->all(), $e->getMessage());
            return $this->sendError('app.network_failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("deleteCard", null, $request->all(), $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/card/{card_id}",
     *     summary="Card detail by card_id",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="card_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *      @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function showCard($card_id, Request $request)
    {
        try {

            $buyer = $this->buyer->getProfileBuyer($request->buyer_id);

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (!isset($buyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            if (!isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.customer_stripe')]));

            try {
                $customer = $this->stripeClient->customers->retrieve(
                    $buyer['stripe_customer_id'],
                    []
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("showCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
            }

            try {
                $card = $this->stripeClient->customers->retrieveSource(
                    $customer['id'],
                    $card_id,
                    []
                );
                return $this->sendSuccessResponse($card);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("showCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.card')]), Response::HTTP_OK);
            }
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("showCard", null, $request->all(), $e->getMessage());
            return $this->sendError('app.network_failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("showCard", null, $request->all(), $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Display the specified resource.
     *
     *  @OA\Get(
     *     path="/api/card/buyer/{buyer_id}/default",
     *     summary="Default Card info",
     *     tags={"Card"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="buyer_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="2",
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     *  )
     * @return \Illuminate\Http\Response
     */
    public function showCardDefault($buyer_id)
    {
        try {

            $buyer = $this->buyer->getProfileBuyer($buyer_id);

            $user = Auth::guard('users')->user();
            if ($buyer->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            if (!isset($buyer))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            if (!isset($buyer['stripe_customer_id']))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.customer_stripe')]));

            try {
                $customer = $this->stripeClient->customers->retrieve(
                    $buyer['stripe_customer_id'],
                    []
                );
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("showCard", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.customer_stripe')]), Response::HTTP_OK);
            }

            try {
                $card = $this->stripeClient->customers->retrieveSource(
                    $customer['id'],
                    $customer['default_source'],
                    []
                );
                return $this->sendSuccessResponse($card);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $error_code = $e->getError()->code;
                $this->log("showCardDefault", null, ['error_code' => $e->getError()->code], $e->getMessage());
                return $this->sendError(__('app.'.$error_code, ['attribute' => __('app.card')]), Response::HTTP_OK);
            }
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->log("showCardDefault", null, ['buyer_id' => $buyer_id], $e->getMessage());
            return $this->sendError('app.network_failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $this->log("showCardDefault", null, ['buyer_id' => $buyer_id], $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
