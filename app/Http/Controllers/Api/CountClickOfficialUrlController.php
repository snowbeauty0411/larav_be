<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\NumberClickOfficialUrl;
use App\Models\Service;
use App\Constants\UserConst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CountClickOfficialUrlController extends BaseController
{
    protected $numberClickOfficialUrl;
    protected $service;

    public function __construct(
        NumberClickOfficialUrl $numberClickOfficialUrl,
        Service $service
    ) {
        $this->numberClickOfficialUrl = $numberClickOfficialUrl;
        $this->service = $service;
    }


    /**
     * Display invoice of buyer by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/count/click/official-url/{hash_id}",
     *     summary="Count Number Click Official Url",
     *     tags={"Service Statistical"},
     *      @OA\Parameter(
     *         description="Service hash ID ",
     *         in="path",
     *         name="hash_id",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="Bad Request",
     *     ),
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function countClickOfficialUrl($hash_id)
    {
        try {
            $service = $this->service->findHashId($hash_id);
            if (!$service) return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.service')]));
            $service_id = $service->id;
            $last_number_click = $this->numberClickOfficialUrl->findLastByService($service_id);


            if (!auth(UserConst::USER_GUARD)->user()) {
                $this->numberClickOfficialUrl->countNumberClickOfficialUrl($last_number_click, $service_id);
            } elseif (auth(UserConst::USER_GUARD)->user() && auth(UserConst::USER_GUARD)->user()->id != $service->seller_id) {
                $this->numberClickOfficialUrl->countNumberClickOfficialUrl($last_number_click, $service_id);
            }

            return $this->sendSuccessResponse($this->numberClickOfficialUrl->findLastByService($service_id));
        } catch (Exception $e) {
            $this->log("countClickOfficialUrl", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
