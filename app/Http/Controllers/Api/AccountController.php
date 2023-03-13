<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Account;
use App\Models\Buyer;
use App\Models\Seller;
use App\Models\VerifyAccountIdentity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\UserConst;
use App\Models\Payment;
use App\Models\ServiceStoreBuyer;
use App\Models\TransferHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountController extends BaseController
{
    protected $account;
    protected $buyer;
    protected $seller;
    protected $serviceStoreBuyer;
    protected $transferHistory;
    protected $verifyAccountIdentity;

    public function __construct(
        Account $account,
        Buyer $buyer,
        Seller $seller,
        ServiceStoreBuyer $serviceStoreBuyer,
        TransferHistory $transferHistory,
        Payment $payment,
        VerifyAccountIdentity $verifyAccountIdentity
    ) {
        $this->account = $account;
        $this->buyer = $buyer;
        $this->seller = $seller;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
        $this->transferHistory = $transferHistory;
        $this->payment = $payment;
        $this->verifyAccountIdentity = $verifyAccountIdentity;
    }


    public function updateRules()
    {
        return [
            'phone_number' => 'nullable|string',
            'old_password' => 'nullable|string',
            'new_password' => 'nullable|string',
            'birth_day' => 'nullable|date_format:"Y-m-d"'
        ];
    }

    /**
     *  @OA\Post(
     *     path="/api/account/switch",
     *     summary="Switch account",
     *     tags={"Account"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="type",
     *                             type="string",
     *                             example="SELLER"
     *                         ),
     *        ),
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Successful response",
     *     ),
     *     @OA\Response(
     *        response="403",
     *        description="Forbidden",
     *     ),
     *  )
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function switchAccount(Request $request)
    {
        try {
            $user = auth(UserConst::USER_GUARD)->user();
            if (!$user || !isset($request->type) || empty($request->type)) {
                return $this->sendError(__('app.not_have_permission'), Response::HTTP_FORBIDDEN);
            }
            $account_info = $this->account->getInfoAccountById($user->id);
            if (!$account_info) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));
            }
            $infoUser = array(
                'id' => $account_info->id,
            );
            if ($request->type == 'SELLER') {
                $infoUser = array_merge($infoUser, ['type' => 'BUYER', 'account_name' => $account_info->buyers->account_name]);
                return $this->sendSuccessResponse($infoUser);
            } elseif ($request->type == 'BUYER') {
                $infoUser = array_merge($infoUser, ['type' => 'SELLER', 'account_name' => $account_info->sellers->account_name]);
                return $this->sendSuccessResponse($infoUser);
            }
            return $this->sendError(__('app.not_have_permission'), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/account/setting/info",
     *     summary="get info Account on setting screen",
     *     tags={"Account"},
     *     security={ {"bearer": {}} },
     *     @OA\Response(
     *        response="200",
     *        description="Get setting info Account successful",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="Buyer not found",
     *     )
     * )
     */
    public function infoSettingAccount()
    {
        try {

            $id = auth(UserConst::USER_GUARD)->user()->id;

            $account_info = $this->account->getSampleInfoAccount($id);

            if (!$account_info) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            }

            return $this->sendSuccessResponse($account_info);
        } catch (Exception $e) {
            $this->log('infoSettingBuyer', null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     *     @OA\Patch(
     *     path="/api/account/setting",
     *     summary="Setting Account",
     *     tags={"Account"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="phone_number",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="old_password",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="new_password",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="birth_day",
     *                             type="string",
     *                             example="1995-01-01"
     *                         ),
     *                         @OA\Property(
     *                             property="message_mail_flg",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="transaction_mail_flg",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="favorite_service_mail_flg",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="recommend_service_mail_flg",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="system_maintenance_mail_flg",
     *                             type="integer",
     *                             example=1
     *                         ),
     *
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Setting buyer successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function settingAccount(Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;

            $validator = Validator::make($request->all(), $this->updateRules());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $account = $this->account->find($id);

            if (!$account) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            }

            DB::beginTransaction();

            if (isset($request->old_password)) {
                if (Hash::check($request['old_password'], auth(UserConst::USER_GUARD)->user()->password)) {
                    $this->account->find($id)->update([
                        'password' => $request->new_password
                    ]);
                } else {
                    return $this->sendError('古いパスワードが正しくありません。');
                }
            }

            $request->request->remove('old_password');
            $request->request->remove('new_password');

            $this->account->updateAccount($id, $request->all());
            
            if ($request->birth_day != $account['birth_day']) {
                $this->account->updateAccount($id, array(
                    'admin_check_date' => null,
                    'identity_verification_status' => 1
                ));
                //delete file
                $file_delete = $this->verifyAccountIdentity->getAllFilesByAccountId($id);
                if ($file_delete) {
                    $path1 = 'identity/' . $file_delete['account_id'] . '/' . $file_delete->file1;
                    $dir = 'identity/' . $file_delete['account_id'];
                    if (Storage::disk('private')->exists($path1)) {
                        Storage::disk('private')->delete($path1);
                    }
                    $path2 = 'identity/' . $file_delete['account_id'] . '/' . $file_delete->file2;
                    if (Storage::disk('private')->exists($path2)) {
                        Storage::disk('private')->delete($path2);
                    }
                    if ( Storage::disk('private')->exists($dir)) {
                        Storage::disk('private')->deleteDirectory($dir);
                    }
                    $this->verifyAccountIdentity->deleteFileByAccountId($id);
                }
            }

            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.account_setting')]));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log('settingAccount', null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *   @OA\Get(
     *     path="/api/account/{id}/info-withdrawal",
     *     summary="Get info withdrawal by account_id",
     *     tags={"Account"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="AccountID need to input info",
     *         in="path",
     *         name="id",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     )
     * )
     */
    public function infoWithdrawalAccount($id)
    {
        try {
            $user = Auth::guard(UserConst::USER_GUARD)->user();
            $account = $this->account->find($id);

            if (!$account) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));

            if ($account->id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $serviceStoreBuyer = $this->serviceStoreBuyer->findByBuyer($account->id);

            $serviceStoreSeller = $this->serviceStoreBuyer->findBySeller($account->id);

            $transferHistory = $this->transferHistory->findTransferRegisterBySeller($account->id);

            $discardAmount = $this->payment->getAmountCurrentBySeller($account->id);


            $results = [];

            $results['buyer_using'] = $serviceStoreBuyer ? 1 : 0;
            $results['seller_selling'] = $serviceStoreSeller ? 1 : 0;
            $results['seller_transfer'] = $transferHistory ? 1 : 0;
            $results['discard_amount'] = $discardAmount;

            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log('infoWithdrawalAccount', null, ['account_id' => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *     @OA\Patch(
     *     path="/api/account/{id}/withdrawal",
     *     summary="withdrawal Account",
     *     tags={"Account"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *          name="id",
     *          description="AccountID need to input",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="reason_withdrawal",
     *                             type="string",
     *                             example="reason_withdrawal"
     *                         ),
     *
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
     * )
     *
     */
    public function withdrawalAccount($id, Request $request)
    {
        try {
            $user = Auth::guard(UserConst::USER_GUARD)->user();
            $account = $this->account->find($id);

            if (!$account) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));

            if ($account->id != $user->id) return $this->sendError(__('app.not_have_permission'), Response::HTTP_UNAUTHORIZED, 401);

            $serviceStoreBuyer = $this->serviceStoreBuyer->findByBuyer($account->id);
            if ($serviceStoreBuyer) return $this->sendError(__('app.buyer_using'));

            $serviceStoreSeller = $this->serviceStoreBuyer->findBySeller($account->id);
            if ($serviceStoreSeller) return $this->sendError(__('app.seller_selling'));

            $transferHistory = $this->transferHistory->findTransferRegisterBySeller($account->id);
            if ($transferHistory) return $this->sendError(__('app.seller_transfer_register'));

            $data['date_withdrawal'] = Carbon::now()->toDateTimeString();
            if ($request->reason_withdrawal)  $data['reason_withdrawal'] = $request->reason_withdrawal;

            $this->account->updateAccount($id, $data);
            
            $info = $this->buyer->getProfileBuyer($account->id);

            if ($info->account['transaction_mail_flg']) {
                $title = '【subsQ】退会が完了しました';
                $data = [
                    'buyer_name' => $info['account_name'],
                ];
                $this->sendEmail('email.email-notify-withdraw', $info->account['email'], $data, $title);
            }
            
            return $this->sendSuccess(__('app.withdrawal_success'));
        } catch (Exception $e) {
            $this->log('withdrawalAccount', null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
