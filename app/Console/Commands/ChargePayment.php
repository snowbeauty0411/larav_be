<?php

namespace App\Console\Commands;

use App\Constants\ServiceConst;
use App\Http\Controllers\Api\BaseController;
use App\Models\Account;
use Illuminate\Console\Command;
use App\Models\ActionPayment;
use App\Models\Buyer;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCourse;
use App\Models\ServiceStoreBuyer;
use App\Models\ShippingInfo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class ChargePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:stripe';
    protected $user;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'payment stripe 2p';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ActionPayment $actionPayment,
        Buyer $buyer,
        ServiceCourse $serviceCourse,
        StripeClient $stripeClient,
        ShippingInfo $shippingInfo,
        ServiceStoreBuyer $serviceStoreBuyer,
        Payment $payment,
        Service $service,
        Delivery $delivery,
        Account $account,
        BaseController $baseController
    ) {
        $this->actionPayment = $actionPayment;
        $this->buyer = $buyer;
        $this->serviceCourse = $serviceCourse;
        $this->stripeClient = $stripeClient;
        $this->shippingInfo = $shippingInfo;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->payment = $payment;
        $this->service = $service;
        $this->delivery = $delivery;
        $this->account = $account;
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

            DB::beginTransaction();
            $currentDate = Carbon::now()->toDateString();
            $list_action = $this->actionPayment->findByChargeAt($currentDate);

            foreach ($list_action as $action_payment) {

                $serviceStoreBuyer = $this->serviceStoreBuyer->find($action_payment['service_store_buyer_id']);

                $buyer = $serviceStoreBuyer->buyer;

                $account = $this->account->find($buyer['account_id']);

                $service_course =  $serviceStoreBuyer->serviceCourses;

                $payment_charge = $this->payment->getUnpaidByStoreID($serviceStoreBuyer['id']);

                $service = $this->service->find($service_course['service_id']);

                $interval_delivery = $service->serviceDelivery;

                if ($service->service_type_id === 1) {
                    $charge_at_next = $interval_delivery->interval == 0 ? Carbon::parse($action_payment['charge_at'])->addWeek()->toDateString() : Carbon::parse($action_payment['charge_at'])->addMonths($interval_delivery->month_delivery)->toDateString();
                } else {
                    $charge_at_next = Carbon::parse($action_payment['charge_at'])->addMonths($service_course['cycle'])->toDateString();
                }

                $price = $service_course['price'];
                $service_fee = ($price * ServiceConst::SERVICE_FEE) / 100;
                $amount = floor($service_course['price'] + $service_fee);

                if (!$action_payment['skip']) { // check not skip

                    if ($buyer && $service_course && $serviceStoreBuyer &&  $serviceStoreBuyer['status'] < 2 && $payment_charge) {

                        try {
                            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

                            $charge = $stripe->charges->create([
                                'currency' => 'JPY',
                                'amount' =>  $amount,
                                'customer' => $buyer['stripe_customer_id'],
                                'card' => $action_payment['card_id'],
                                'description' => $service_course['name'],
                            ]);

                            Log::channel('paymentlogcustom')->info(json_encode([
                                'buyer_id' => $buyer['account_id'],
                                'time' => Carbon::now()->format('Y-m-d H:i:s'),
                                'price' => $amount,
                                'store_buyer_id' => $serviceStoreBuyer['id'],
                                'charge_id' => $charge->id,
                                'card_id' => $action_payment['card_id'],
                            ]));

                        } catch (\Stripe\Exception\CardException $e) {
                            $serviceStoreBuyer['status'] = 2;
                            $serviceStoreBuyer['end'] = Carbon::parse($action_payment['charge_at'])->toDateString();
                            $serviceStoreBuyer->save();
                            if ($account['transaction_mail_flg']) {
                                $title = '【subsQ】お支払いのお知らせ';
                                $data = [
                                    'buyer_name' => $buyer['account_name'],
                                    'course_id' => $service_course['id'],
                                    'course_name' => $service_course['name'],
                                ];
                                $this->baseController->sendEmail('email.email-payment-failed', $buyer['email'], $data, $title);
                            }
                            $this->baseController->log("SchedulePayment1", null, ['error_code' => $e->getError()->code], $e->getMessage());
                            return;
                        } catch (\Stripe\Exception\InvalidRequestException $e) {
                            $serviceStoreBuyer['status'] = 2;
                            $serviceStoreBuyer['end'] = Carbon::parse($action_payment['charge_at'])->toDateString();
                            $serviceStoreBuyer->save();
                            if ($account['transaction_mail_flg']) {
                                $title = '【subsQ】お支払いのお知らせ';
                                $data = [
                                    'buyer_name' => $buyer['account_name'],
                                    'course_id' => $service_course['id'],
                                    'course_name' => $service_course['name'],
                                ];
                                $this->baseController->sendEmail('email.email-payment-failed', $buyer['email'], $data, $title);
                            }
                            $this->baseController->log("SchedulePayment2", null, ['error_code' => $e->getError()->code], $e->getMessage());
                            return;
                        }
                        // update status payment
                        $this->payment->updatePayment(
                            $payment_charge->id,
                            [
                                'payment_status' => 1,
                                'stripe_charge_id' => $charge->id
                            ]
                        );
                        // update status QR
                        $serviceStoreBuyer['end'] = $charge_at_next;
                        $serviceStoreBuyer['flagQR'] = null;
                        $serviceStoreBuyer->save();
                        
                        // create payment next cycle
                        $payment_next = new Payment();
                        $payment_next['id'] = $this->payment->generatePaymentId();
                        $payment_next['service_store_buyer_id'] = $action_payment->service_store_buyer_id;
                        $payment_next['sub_total'] = $price;
                        $payment_next['service_fee'] = $service_fee;
                        $payment_next['total'] = $amount;
                        $payment_next['pay_expire_at_date'] = $charge_at_next;
                        $payment_next['stripe_charge_id'] = null;
                        $payment_next['card_id'] = $action_payment['card_id'];
                        $payment_next['created_at'] = now();
                        $payment_next['updated_at'] =  now();
                        $payment_next['payment_status'] = 0;
                        $payment_next->save();

                        if ($service->service_type_id == 1) { // service is delivery
                            // create delivery interval current
                            $new_delivery = new Delivery();
                            $new_delivery['service_store_buyer_id'] = $serviceStoreBuyer->id;
                            $new_delivery['payment_id'] = $payment_next->id;
                            $new_delivery['delivery_address'] = $action_payment['delivery_address'];
                            $new_delivery['buyer_full_name'] = $action_payment['buyer_full_name'];
                            $new_delivery['estimated_date'] = $interval_delivery->interval == 0 ? Carbon::parse($charge_at_next)->addWeek()->toDateString() : Carbon::parse($charge_at_next)->addMonths($interval_delivery->month_delivery)->toDateString();
                            $new_delivery['delivery_status'] = 1;
                            $new_delivery->save();
                        }
                        // update date charge next cycle
                        $action_payment['charge_at'] = $charge_at_next;
                        $action_payment->save();

                        if ($account['transaction_mail_flg']) { // check flag send mail
                            $title = '【subsQ】お支払いのお知らせ';
                            $data = [
                                'buyer_name' => $buyer['account_name'],
                                'course_id' => $service_course['id'],
                                'course_name' => $service_course['name'],
                            ];
    
                            $this->baseController->sendEmail('email.email-payment-success', $buyer['email'], $data, $title);
                        }
                    }
                } else { // handle skip
                    // update status QR
                    $serviceStoreBuyer['end'] = $charge_at_next;
                    $serviceStoreBuyer['flagQR'] = null;
                    $serviceStoreBuyer->save();

                    // create payment next cycle
                    $payment_next = new Payment();
                    $payment_next['id'] = $this->payment->generatePaymentId();
                    $payment_next['service_store_buyer_id'] = $action_payment->service_store_buyer_id;
                    $payment_next['sub_total'] = $price;
                    $payment_next['service_fee'] = $service_fee;
                    $payment_next['total'] = $amount;
                    $payment_next['pay_expire_at_date'] = $charge_at_next;
                    $payment_next['stripe_charge_id'] = null;
                    $payment_next['card_id'] = $action_payment['card_id'];
                    $payment_next['created_at'] = now();
                    $payment_next['updated_at'] =  now();
                    $payment_next['payment_status'] = 0;
                    $payment_next->save();

                    if ($service->service_type_id == 1) {
                        // create delivery interval current
                        $new_delivery = new Delivery();
                        $new_delivery['service_store_buyer_id'] = $serviceStoreBuyer->id;
                        $new_delivery['payment_id'] = $payment_next->id;
                        $new_delivery['delivery_address'] = $action_payment['delivery_address'];
                        $new_delivery['buyer_full_name'] = $action_payment['buyer_full_name'];
                        $new_delivery['estimated_date'] = $interval_delivery->interval == 0 ? Carbon::parse($charge_at_next)->addWeek()->toDateString() : Carbon::parse($charge_at_next)->addMonths($interval_delivery->month_delivery)->toDateString();
                        $new_delivery['delivery_status'] = 1;
                        $new_delivery->save();
                    }
                    // update action payment next cycle
                    $action_payment['skip'] = false;
                    $action_payment['charge_at'] = $charge_at_next;
                    $action_payment->save();
                    Log::channel('paymentlogcustom')->info(Carbon::now() . ' Skip charge');
                }
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->baseController->log("ChargePaymentAuto", null, null, $e->getMessage());
        }
    }
}
