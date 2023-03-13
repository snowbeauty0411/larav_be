<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TestPaymentManagementController extends BaseController
{
    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }


    public function test($service_id){
        try{
           $data = $this->payment->getAllPaymentByService($service_id);
           return $this->sendSuccessResponse($data);
        }catch(Exception $e){
            $this->log("test", null, $service_id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
   
}
