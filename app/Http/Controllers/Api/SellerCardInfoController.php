<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerCardInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SellerCardInfoController extends BaseController
{
    protected  $sellerCardInfo;

    public function __construct(
        SellerCardInfo $sellerCardInfo
    )
    {
        $this->sellerCardInfo =  $sellerCardInfo;
    }

    public function rules()
    {
        return [
            'seller_id' => 'required|integer|exists:sellers,account_id',
            'bank_name' => 'required',
            'account_type' => 'required',
            'branch_code' => 'required|numeric|regex:/^[0-9]+$/',
            'account_number' => 'required|numeric|regex:/^[0-9]+$/',
            'last_name_account' => 'required|string|regex:/^([ァ-ヾ])/',
            'first_name_account' => 'required|string|regex:/^([ァ-ヾ])/',
        ];
    }

    public function updateRules()
    {
        return [
            'bank_name' => 'required',
            'account_type' => 'required',
            'branch_code' => 'required|numeric|regex:/^[0-9]+$/',
            'account_number' => 'required|numeric|regex:/^[0-9]+$/',
            'first_name_account' => 'required|string|regex:/^([ァ-ヾ])/',
            'last_name_account' => 'required|string|regex:/^([ァ-ヾ])/',
        ];
    }

    /**
     * Display a newly created resource in storage.
     *  @OA\Post(
     *     path="/api/seller-card/create",
     *     summary="Create seller card",
     *     tags={"Seller Card"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="seller_id",
     *                  type="integer",
     *                  example="2"
     *             ),
     *             @OA\Property(
     *                  property="bank_id",
     *                  type="integer",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="bank_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="account_type",
     *                  type="integer",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="branch_code",
     *                  type="string",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="account_number",
     *                  type="string",
     *                  example="12345678"
     *             ),
     *             @OA\Property(
     *                  property="last_name_account",
     *                  type="string",
     *                  example="セイ"
     *             ),
     *             @OA\Property(
     *                  property="first_name_account",
     *                  type="string",
     *                  example="メイ"
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
            $data = $request->all();

            $validator = Validator::make($data, $this->rules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            $sellerCardInfo = $this->sellerCardInfo->find($data['seller_id']);

            if ($sellerCardInfo)
                return $this->sendError(__('app.exist', ['attribute' => __('app.seller_card')]));

            $user = Auth::guard('users')->user();
            if ($data['seller_id'] != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $data['id'] = $data['seller_id'];

            $sellerCardInfo = $this->sellerCardInfo->create($data);

            if (isset($sellerCardInfo)) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.seller_card')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.seller_card')]));
            }


        } catch (Exception $e) {
            $this->log("createSellerCard", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/seller-card/{seller_id}",
     *     summary="get card by seller_id",
     *     tags={"Seller Card"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="seller_id",
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
     * @param  int  $seller_id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $sellerCardInfo = $this->sellerCardInfo->find($id);
            if (!isset($sellerCardInfo))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller_card')]));
            return $this->sendSuccessResponse($sellerCardInfo);
        } catch (Exception $e) {
            $this->log("showSellerCard", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @OA\Put(
     *     path="/api/seller-card/edit/{seller_id}",
     *     summary="edit card",
     *     tags={"Seller Card"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="seller_id",
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
     *                  property="bank_id",
     *                  type="integer",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="bank_name",
     *                  type="string",
     *                  example=""
     *             ),
     *             @OA\Property(
     *                  property="account_type",
     *                  type="integer",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="branch_code",
     *                  type="string",
     *                  example="1"
     *             ),
     *             @OA\Property(
     *                  property="account_number",
     *                  type="string",
     *                  example="12345678"
     *             ),
     *             @OA\Property(
     *                  property="last_name_account",
     *                  type="string",
     *                  example="セイ"
     *             ),
     *             @OA\Property(
     *                  property="first_name_account",
     *                  type="string",
     *                  example="メイ"
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
            $data = $request->all();

            $validator = Validator::make($data, $this->updateRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            $sellerCardInfo = $this->sellerCardInfo->find($id);

            if (!isset($sellerCardInfo))
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller_card')]));

            $user = Auth::guard('users')->user();
            if ($sellerCardInfo->id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $sellerCardInfo = $this->sellerCardInfo->updateById($id, $data);

            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.seller_card')]));

        } catch (Exception $e) {
            $this->log("updateSellerCard", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
