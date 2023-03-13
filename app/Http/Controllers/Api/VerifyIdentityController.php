<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\Buyer;
use App\Models\Seller;
use App\Models\VerifyAccountIdentity;
use App\Models\ServiceStoreBuyer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VerifyIdentityController extends BaseController
{
    protected $verifyAccountIdentity;
    protected $account;
    protected $buyer;
    protected $seller;
    protected $serviceStoreBuyer;

    public function __construct(
        VerifyAccountIdentity $verifyAccountIdentity,
        Account $account,
        Buyer $buyer,
        Seller $seller,
        ServiceStoreBuyer $serviceStoreBuyer
    ) {
        $this->verifyAccountIdentity = $verifyAccountIdentity;
        $this->account = $account;
        $this->buyer = $buyer;
        $this->seller = $seller;
        $this->serviceStoreBuyer = $serviceStoreBuyer;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function uploadRules()
    {
        return [
            'file1' => 'required',
            'file2' => 'required',
            'account_id' => 'required|integer',
            'identity_file_type' => 'integer|min:1|max:5'
        ];
    }

    /**
     * Set type for file
     *
     * @param [type] $file_type
     * @return integer
     */
    public function setFileTypeId($file_type)
    {
        $file_type = strtolower($file_type);
        if ($file_type === 'jpg' || $file_type === 'jpeg') {
            $type_id = 1;
        } elseif ($file_type === 'png') {
            $type_id = 2;
        } elseif ($file_type === 'pdf') {
            $type_id = 3;
        } else {
            return $this->sendError('ファイルの形式が正しくありません');
        }
        return $type_id;
    }

    //The update status for field identity_verification_status is as follows
    // 1 => Not verified, 2 => waiting for verification, 3 => Reject ,4 => verified

    /**
     * Upload file verify identity function
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/identity/upload-file",
     *     summary="Upload file verify identity",
     *     tags={"Verify Identity"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="ID of account need to display",
     *                     property="account_id",
     *                     type="integer",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     description="type file identity with buyer: 1-運転免許証/運転経歴証明書, 2-マイナンバーカード, 3-パスポート, 4-在留カード; seller: 5-登記簿謄本",
     *                     property="identity_file_type",
     *                     type="integer",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     description="File to upload",
     *                     property="file1",
     *                     type="file",
     *                 ),
     *                  @OA\Property(
     *                     description="File to upload",
     *                     property="file2",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     description="file name delete",
     *                     property="delete_file_names",
     *                      type="array",
     *                      @OA\Items(type="string")
     *                 ),
     *                 required={"file", "account_id"}
     *             )
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
    public function uploadFile(Request $request)
    {
        try {

            $validator = Validator::make(array_filter($request->all()), $this->uploadRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            DB::beginTransaction();
            $account_id = $request->account_id;

            $check_have_file = $this->verifyAccountIdentity->getAllFilesByAccountId($account_id);
            
            if ($check_have_file) return $this->sendError(__('app.exist',['attribute'=>__('app.file')]));

            $file_saved = new VerifyAccountIdentity();
            $file_saved["account_id"] = $account_id;
            $file_saved["identity_file_type"] = $request->identity_file_type;

            //save file
            if ($request->hasFile('file1')) {
                $rules = array_merge($this->uploadRules(), ['file1' => 'mimes:pdf,jpg,png|max:10240']);
                $validator = Validator::make(array_filter($request->all()), $rules);
                $errors = $validator->errors();
                if ($errors->first()) return $this->sendError($errors->first());
                $extension1 = $request->file1->getClientOriginalExtension();
                $fileName1 = $account_id . '-' . date('mdHis') . uniqid('-') . '.' . $extension1;
                Storage::disk('local')->put('/private/identity/'.$account_id.'/'.$fileName1, file_get_contents($request->file1));
                $file_saved["file1"] = $fileName1;
            }
            if ($request->hasFile('file2')) {
                $rules = array_merge($this->uploadRules(), ['file2' => 'mimes:pdf,jpg,png|max:10240']);
                $validator = Validator::make(array_filter($request->all()), $rules);
                $errors = $validator->errors();
                if ($errors->first()) return $this->sendError($errors->first());
                $extension2 = $request->file2->getClientOriginalExtension();
                $fileName2 = $account_id . '-' . date('mdHis') . uniqid('-') . '.' . $extension2;
                Storage::disk('local')->put('/private/identity/'.$account_id.'/'.$fileName2, file_get_contents($request->file2));
                $file_saved["file2"] = $fileName2;
            }

            $file_saved = $file_saved->save();

            //update status buy buyer and seller
            $this->account->updateAccount($account_id, array(
                    'admin_check_date' => null,
                    'identity_verification_status' => 2
                ));

            DB::commit();
            return $this->sendSuccess(__('app.upload_success',['attribute'=>__('app.file')]));
        } catch (Exception $e) {
            $this->log("uploadFileIdentityAccount", null, ["request" => $request->all()], $e->getMessage());
            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rules()
    {
        return [
            'id' => 'required|integer',
            'type' => 'required|string'
        ];
    }
    /**
     *     @OA\Patch(
     *     path="/api/admin/user/identity/confirm/{id}",
     *     summary="Admin confirm identification file",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="確認済み",
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
    public function confirmIdentificationVerify($id)
    {
        try {
            DB::beginTransaction();
            $account_info = $this->account->find($id);
            if (!$account_info) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.account')]));

            $this->account->updateAccount($id, array(
                'admin_check_date' => Carbon::now(),
                'identity_verification_status' => 4
            ));

            $account_files = $this->verifyAccountIdentity->getAllFilesByAccountId($id);

            if ($account_files) {
                $this->verifyAccountIdentity->updateVerifyAccountIdentity(
                    $account_files['id'],
                    array(
                        'approval_at' => Carbon::now()->toDateTimeString(),
                        'delete_date' => Carbon::now()->addDays(30)->toDateTimeString()
                    )
                );
            }

            DB::commit();
            return $this->sendSuccess(__('app.verified_identity'));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("confirmIdentificationVerify", null, ["account_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/user/identity/reject/{id}",
     *     summary="Admin reject identification file",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
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
    public function rejectIdentificationVerify($id)
    {
        try {
            DB::beginTransaction();
            $account_info = $this->account->find($id);
            if (!$account_info) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.account')]));

            $this->account->updateAccount($id, array(
                'admin_check_date' => Carbon::now(),
                'identity_verification_status' => 3
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

            //send mail
            $email = $account_info->email;
            $data = array(
                'APP_URL' => config('app.url'),
            );
            $title = __('app.title_mail_user_identification_reject');
            $this->sendEmail('email.email-user-identification-reject', $email, $data, $title);
            DB::commit();
            return $this->sendSuccess(__('app.refuse_to_verify_identity'));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("rejectIdentificationVerify", null, ["account_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Patch(
     *     path="/api/admin/user/pending/{id}",
     *     summary="Admin block account",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
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
    public function blockAccount($id)
    {
        try {
            $account_info = $this->account->find($id);
            if (!$account_info) return $this->sendError(__('app.not_exist', ['attribute'  => __('app.account')]));

            $serviceStoreBuyers = $this->serviceStoreBuyer->findByBuyer($id);
            if ($serviceStoreBuyers)
                return $this->sendError(__('app.account_using_service'));
            $this->account->updateAccount($id, array(
                'blocked_at' => !$account_info['is_blocked'] ? Carbon::now() : null,
                'is_blocked' => !$account_info['is_blocked'],
            ));
            return $this->sendSuccess(__('app.success'));
        } catch (Exception $e) {
            $this->log("blockAccount", null, ["account_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function filterAdminGetUserRules()
    {
        return [
            'id' => 'nullable|integer',
            'full_name' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'sort' => 'integer|nullable',
            'sort_type' => 'integer|nullable',
            'identity_verification_status' => 'integer|min:1|max:4|nullable'
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/user/buyer/list",
     *     summary="Admin get all buyer",
     *     tags={"Admin"},
     *      security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *               @OA\Property(
     *               property="email",
     *               example="",
     *               type="string",
     *              ),
     *              @OA\Property(
     *              property="phone_number",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="account_name",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="full_name",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="is_blocked",
     *              example="",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="identification_verify_status",
     *              example="",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort_type",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="per_page",
     *              example="10",
     *              type="integer",
     *              ),
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
    public function getListBuyers(Request $request)
    {
        try {
            $credentials = $request->all();
            $validator = Validator::make($credentials, $this->filterAdminGetUserRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());
            $results = $this->account->getAllBuyers($request->per_page, $request);
            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log("getListBuyer", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/user/seller/list",
     *     summary="Admin get all seller",
     *     tags={"Admin"},
     *      security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="page number need to input info",
     *         in="query",
     *         name="page",
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *               @OA\Property(
     *               property="email",
     *               example="",
     *               type="string",
     *              ),
     *              @OA\Property(
     *              property="phone_number",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="account_name",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="full_name",
     *              example="",
     *              type="string",
     *              ),
     *              @OA\Property(
     *              property="is_blocked",
     *              example="",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="identification_verify_status",
     *              example="",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="sort_type",
     *              example="1",
     *              type="integer",
     *              ),
     *              @OA\Property(
     *              property="per_page",
     *              example="10",
     *              type="integer",
     *              ),
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
    public function getListSellers(Request $request)
    {
        try {
            $credentials = $request->all();
            $validator = Validator::make($credentials, $this->filterAdminGetUserRules());
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());
            $results = $this->account->getAllSellers($request->per_page, $request);
            return $this->sendSuccessResponse($results);
        } catch (Exception $e) {
            $this->log("getListSeller", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadAvatarProfile(Request $request)
    {
        try {
            $rules = [
                'file' => 'required|nullable|mimes:jpeg,jpg,png|max:10240',
                'account_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first(), Response::HTTP_OK);

            $account = $this->account->find($request->account_id);
            $old_image = $account['profile_image_url'];
            // check account not exist
            if (!isset($account)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));
            if (!$request->hasFile('file')) return $this->sendError(__('app.media_type'));

            $file = $request->file;
            $mimeType = $file->getMimeType();
            if (0 !== strpos($mimeType, 'image')) {
                return $this->sendError(__('app.media_type'));
            }
            $extension = $file->getClientOriginalExtension();
            $fileName = $account['id'] . '-' . date('mdHis') . uniqid('-') . '.' . $extension;

            //update field profile_image_url in buyer
            $file->move(public_path('storage/avatar'), $fileName);
            $account['profile_image_url'] = 'avatar/' . $fileName;
            $account->updateAccount($account['id'], $account->toArray());

            //delete image old
            Storage::disk('public')->delete($old_image);

            return $this->sendSuccessResponse(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.avatar')]));
        } catch (Exception $e) {
            $this->log("uploadAvatarProfile", null, ["request" => $request->all()], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getResourcePublicFile($fileName)
    {
        try {
            $path = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . 'avatar/' . $fileName;
            if (file_exists($path)) {
                return response()->file($path);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.file')]));
            }
        } catch (Exception $e) {
            $this->log("getResourcePublicFile", null, $fileName, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Get(
     *     path="/api/account/{account_id}/{fileName}/{index}",
     *     summary="Get file private Resource",
     *     tags={"Verify Identity"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="account_id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *       @OA\Parameter(
     *         description="file name need to display",
     *         in="path",
     *         name="fileName",
     *         required=true,
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *       @OA\Parameter(
     *         description="index file (1: file1; 2: file2)",
     *         in="path",
     *         name="index",
     *         required=true,
     *         example="1",
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
    public function getResourcePrivateFile($account_id, $fileName, $index)
    {
        try {
            $path = Storage::disk('private')->getDriver()->getAdapter()->getPathPrefix() . 'identity/' . $account_id . '/' . $fileName;
            if (Auth::guard('admins')->check()) {
                if (file_exists($path)) {
                    return response()->file($path);
                } else {
                    return $this->sendError(__('app.not_exist', ['attribute' => __('app.file')]));
                }
            } else if (Auth::user()->id == $account_id) {
                if ($index == 1) {
                    $file = $this->verifyAccountIdentity->getFile1ByAccountIdAndFileName($account_id, $fileName);
                } elseif ($index == 2) {
                    $file = $this->verifyAccountIdentity->getFile2ByAccountIdAndFileName($account_id, $fileName);
                }
                if (isset($file) && file_exists($path)) {
                    return response()->file($path);
                } else {
                    return $this->sendError(__('app.not_exist', ['attribute' => __('app.file')]));
                }
            } else {
                return $this->sendError(__('app.not_have_permission'));
            }
        } catch (Exception $e) {
            $this->log("getResourcePrivateFile", null, $fileName, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Get(
     *     path="/api/identity/account/file/{id}",
     *     summary="Get file by account id",
     *     tags={"Verify Identity"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
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
    public function getFileIdentityByAccountId($id)
    {
        try {
            
            $file = $this->verifyAccountIdentity->getAllFilesByAccountId($id);

            if ($file) {
                if ($file['file1']) {
                    $file['path1'] = 'account/' . $id . '/' . $file['file1'];
                    $file['url1'] = route('getResourcePrivateFile', ['account_id' => $id, 'fileName' => $file['file1'], 1]);
                }
                if ($file['file2']) {
                    $file['path2'] = 'account/' . $id . '/' . $file['file2'];
                    $file['url2'] = route('getResourcePrivateFile', ['account_id' => $id, 'fileName' => $file['file2'], 2]);
                }
            }
            return $this->sendSuccessResponse($file);
        } catch (Exception $e) {
            $this->log("getFileIdentityByAccountId", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Get(
     *     path="/api/admin/account-info/{id}",
     *     summary="Get file by account id",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *       @OA\Parameter(
     *         description="ID of account need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
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
    public function getAccountInfo($id)
    {
        try {
            $account = $this->account->accountInfo($id);
            // check account not exist
            if (!isset($account)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));
            return $this->sendSuccessResponse($account);
        } catch (Exception $e) {
            $this->log("getAccountInfo", null, $id, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Get(
     *     path="/api/service/{id}/{fileName}",
     *     summary="Get image service",
     *     tags={"Service"},
     *       @OA\Parameter(
     *         description="ID of service need to display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *       @OA\Parameter(
     *         description="file name need to display",
     *         in="path",
     *         name="fileName",
     *         required=true,
     *         @OA\Schema(
     *         type="string"
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
     *    @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function getImageService($id, $fileName)
    {
        try {
            $path = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . 'services/' . $id . '/' . $fileName;
            if (file_exists($path)) {
                return response()->file($path);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.file')]));
            }
        } catch (Exception $e) {
            $this->log("getResourcePrivateFile", null, $fileName, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
