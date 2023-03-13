<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Payment;
use App\Models\Seller;
use App\Models\TransferHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransferHistoryController extends BaseController
{
    protected $seller;
    protected $transferHistory;
    protected $payment;
    protected $buyer;

    public function __construct(
        Seller $seller,
        TransferHistory $transferHistory,
        Payment $payment,
        Buyer $buyer
    )
    {
        $this->seller =  $seller;
        $this->transferHistory = $transferHistory;
        $this->payment = $payment;
        $this->buyer = $buyer;
    }

    public function rules()
    {
        return [
            'transfer_amount' => 'required|integer|min:1',
        ];
    }

        /**
     * Display the specified resource.
     * @OA\Post(
     *     path="/api/admin/transfer/list",
     *     summary="Get all transfer history ",
     *     tags={"Transfer History"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          example="1",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="per_page",
     *                  type="integer",
     *                  example="10"
     *              ),
     *              @OA\Property(
     *                  property="seller_name",
     *                  type="string",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="created_at",
     *                  type="string",
     *                  example="2022-05-24"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="integer",
     *                  example=0
     *              ),
     *         )
     *     ),
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
    public function getAllByAdmin(Request $request)
    {
        try {
            $results = $this->transferHistory->getAll($request);
            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log("getAllTransferByAdmin", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/transfer/{id}/completed",
     *     summary="Admin approve completed transfer",
     *     tags={"Transfer History"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of Transfer History need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="123456789",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="success",
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
    public function completedTransfer($id)
    {
        try {
            $transferHistory = $this->transferHistory->find($id);

            if (!$transferHistory) return $this->sendError(__('app.not_exist', ['attribute' => __('app.transfer_history')]));

            $transferHistory['status'] = 1;
            $transferHistory->save();

            return $this->sendSuccess(__('app.action_success', ['action'=>__('app.update'),'attribute' => __('app.transfer_history')]));
        } catch (Exception $e) {
            $this->log("AdminCompletedTransfer", null, ['transfer_history_id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a newly created resource in storage.
     *  @OA\Post(
     *     path="/api/seller/{seller_id}/transfer/create",
     *     summary="Create transfer history",
     *     tags={"Transfer History"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="seller_id need to input info",
     *         in="path",
     *         name="seller_id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *         type="string"
     *        )
     *      ),
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             @OA\Property(
     *                  property="transfer_amount",
     *                  type="integer",
     *                  example="1000"
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
    public function store(Request $request, $id)
    {
        try {
            $data = $request->all();

            $validator = Validator::make($data, $this->rules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            $seller = $this->seller->findByAccountId($id);

            if (!$seller)
                return $this->sendError(__('app.not_exist', ['attribute' => 'seller']));

            $user = Auth::guard('users')->user();
            if ($seller->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $amount_current = $this->payment->getAmountCurrentBySeller($id);
            if ($request->transfer_amount > $amount_current)
                return $this->sendError(__('app.not_transfer'));

            $data['id'] = $this->transferHistory->generateTransferID();
            $data['seller_id'] = $id;
            $data['transfer_fee'] = 250;
            $transferHistory = $this->transferHistory->create($data);

            if (isset($transferHistory)) {
                return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.transfer_history')]));
            } else {
                return $this->sendError(__('app.action_failed', ['action' => __('app.create'), 'attribute' => __('app.transfer_history')]));
            }

        } catch (Exception $e) {
            $this->log("createTransferHistory", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/seller/{seller_id}/transfer/list",
     *     summary="Get transfer history by seller_id",
     *     tags={"Transfer History"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *          name="seller_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          example="10",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          example="1",
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
    public function getAllBySeller($id, Request $request)
    {
        try {
            $seller = $this->seller->findByAccountId($id);

            if (!$seller)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller')]));

            $user = Auth::guard('users')->user();
            if ($seller->account_id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            return $this->sendSuccessResponse($this->transferHistory->getAllBySeller($id, $request->per_page));
        } catch (Exception $e) {
            $this->log("getAllTransferBySeller", null, ['seller_id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
