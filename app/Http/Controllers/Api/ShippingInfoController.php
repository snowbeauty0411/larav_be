<?php

namespace App\Http\Controllers\Api;

use App\Models\ShippingInfo;
use App\Constants\UserConst;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ShippingInfoController extends BaseController
{
    protected $shippingInfo;

    public function __construct(
        ShippingInfo $shippingInfo
    ) {
        $this->shippingInfo = $shippingInfo;
    }

    public function createRules()
    {
        return [
            'buyer_id' => 'required|integer',
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'last_name_kana' => 'required|string',
            'first_name_kana' => 'required|string',
            'phone' => 'required|string',
            'post_code' => 'required|string',
            'address' => 'required|string',
            'is_default' => 'integer|nullable|min:0|max:1'
        ];
    }

    public function updateRules()
    {
        return [
            'last_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name_kana' => 'nullable|string',
            'first_name_kana' => 'nullable|string',
            'phone' => 'nullable|string',
            'post_code' => 'nullable|string',
            'address' => 'nullable|string',
            'is_default' => 'integer|nullable|min:0|max:1'
        ];
    }


    /**
     * Display a listing of the resource.
     *   @OA\Get(
     *     path="/api/shipping-info/list",
     *     summary="Get all shipping info",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $shippingInfos = $this->shippingInfo->all();
            return $this->sendSuccessResponse($shippingInfos);
        } catch (Exception $e) {
            $this->log("shippingInfo_index", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *   @OA\Get(
     *     path="/api/shipping-info/list/{id}",
     *     summary="Get all shipping info by buyer Id",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
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
     * @return \Illuminate\Http\Response
     */
    public function shippingInfoBuyer($id)
    {
        try {
            $shippingInfos = $this->shippingInfo->findByBuyerId($id);
            return $this->sendSuccessResponse($shippingInfos);
        } catch (Exception $e) {
            $this->log("shippingInfo_shippingInfoBuyer", null, ["buyer_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a newly created resource in storage.
     *  @OA\Post(
     *     path="/api/shipping-info/store",
     *     summary="Create shipping info",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="buyer_id",
     *                  type="integer",
     *                  example="2"
     *             ),
     *             @OA\Property(
     *                  property="last_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="first_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="last_name_kana",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="first_name_kana",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="post_code",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="address",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="is_default",
     *                  type="integer",
     *                  example=1
     *             ),
     *        ),
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful response",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  )
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->createRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first(), Response::HTTP_OK);

            //create shippingInfo
            $shipping_info = $this->shippingInfo->create($request->all());

            if ($shipping_info && $shipping_info->is_default == 1) {
                $this->shippingInfo->updateIsDefault($shipping_info->id, $shipping_info->buyer_id);
            }

            if (isset($shipping_info)) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.shipping_info')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.shipping_info')]));
            }
        } catch (Exception $e) {
            error_log($e);
            $this->log("shippingInfo_store", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/shipping-info/{id}",
     *     summary="get shipping info by id",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
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
    public function show($id)
    {
        try {
            $shippingInfo = $this->shippingInfo->find($id);
            if (isset($shippingInfo)) {
                return $this->sendSuccessResponse($shippingInfo);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.shipping_info')]), Response::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->log("shippingInfo_show", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @OA\Put(
     *     path="/api/shipping-info/edit/{id}",
     *     summary="edit shipping info",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="last_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="first_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="last_name_kana",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="first_name_kana",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="phone",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="post_code",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="address",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="is_default",
     *                  type="integer",
     *                  example=0
     *             ),
     *        ),
     *     ),
     *
     *     @OA\Response(
     *        response="200",
     *        description="Successful response",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user_id = auth(UserConst::USER_GUARD)->user()->id;

            $validator = Validator::make(array_filter($request->all()), $this->updateRules());
            $errors = $validator->errors();
            if ($errors->first())
                return $this->sendError($errors->first(), Response::HTTP_OK);

            $shipping_info = $this->shippingInfo->find($id);
            
            if (!isset($shipping_info))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.shipping_info')]), Response::HTTP_OK);
            if ($shipping_info->buyer_id != $user_id)
                return $this->sendError(__('app.not_have_permission'));

            $this->shippingInfo->where('id', $id)->update($request->all());

            if (isset($request->is_default) && $request->is_default == 1) {
                $this->shippingInfo->updateIsDefault($shipping_info->id, $shipping_info->buyer_id);
            }
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.shipping_info')]));
        } catch (Exception $e) {
            $this->log("shippingInfo_update", null, ["request" => $request->all(), 'id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *     path="/api/shipping-info/delete/{id}",
     *     summary="delete shipping info by id",
     *     tags={"Shipping Info"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="not found",
     *     )
     * )
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $shipping_info = $this->shippingInfo->find($id);
            if (!isset($shipping_info))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.shipping_info')]), Response::HTTP_OK);

            if ($shipping_info->is_default == 1) {
                return $this->sendError('このアドレスはデフォルトとして設定されているので削除できません', Response::HTTP_OK);
            }

            $deleted = $shipping_info->delete();
            if ($deleted) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.shipping_info')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.shipping_info')]));
            }
        } catch (Exception $e) {
            $this->log("shippingInfo_delete", null, ['id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
