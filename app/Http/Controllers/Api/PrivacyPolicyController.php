<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\PrivacyPolicy;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrivacyPolicyController extends BaseController
{
    protected $privacyPolicy;

    public function __construct(PrivacyPolicy $privacyPolicy)
    {
        $this->privacyPolicy=$privacyPolicy;
    }

    public function listPrivacyPolicy(){
        try{
            $data = $this->privacyPolicy->listPrivacyPolicy();
            return $this->sendSuccessResponse($data);
        }catch(Exception $e){
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
