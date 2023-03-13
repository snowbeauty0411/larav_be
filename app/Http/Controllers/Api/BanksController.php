<?php

namespace App\Http\Controllers\Api;

use App\Models\Banks;
use App\Models\Branches;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BanksController extends BaseController
{
    protected $banks;
    protected $branches;

    public function __construct(Banks $banks, Branches $branches)
    {
        $this->banks = $banks;
        $this->branches = $branches;
    }

    public function getListBanks(Request $request)
    {
        try {
            $bank = $this->banks->getBank($request);
            return $this->sendSuccessResponse($bank);
        } catch (\Exception $e) {
            error_log($e);
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getListBranches(Request $request)
    {
        try {
            if (empty($request->bank_id)) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.bank')]), Response::HTTP_NOT_FOUND);
            }
            $branch = $this->branches->getBranch($request);
            return $this->sendSuccessResponse($branch);
        } catch (\Exception $e) {
            error_log($e);
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
