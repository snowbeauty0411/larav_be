<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\CompanyInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyInfoController extends BaseController
{
    protected $companyInfo;
    public function __construct(CompanyInfo $companyInfo)
    {
        $this->companyInfo=$companyInfo;
    }


    public function companyInfo(){
        try{
            $data = $this->companyInfo->companyInfo();
            return $this->sendSuccessResponse($data);
        }catch(Exception $e){
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
