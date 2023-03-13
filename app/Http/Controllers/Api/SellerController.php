<?php

namespace App\Http\Controllers\Api;

use App\Constants\UserConst;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Seller;
use App\Models\UrlOfficial;
use App\Models\VerifyAccountIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SendGrid\Mail\To;

class SellerController extends BaseController
{
    protected $seller;
    protected $account;
    protected $urlOfficial;
    protected $verifyAccountIdentity;
    protected $message_thread;
    protected $message;
    protected $admin;

    public function __construct(
        Seller $seller,
        Account $account,
        UrlOfficial $urlOfficial,
        VerifyAccountIdentity $verifyAccountIdentity,
        MessageThread $message_thread,
        Message $message,
        Admin $admin
    ) {
        $this->seller = $seller;
        $this->account = $account;
        $this->urlOfficial = $urlOfficial;
        $this->verifyAccountIdentity = $verifyAccountIdentity;
        $this->message_thread = $message_thread;
        $this->message = $message;
        $this->admin = $admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        return [
            'account_name' => 'string|nullable',
            'url_official' => 'string|nullable',
            'url_facebook' => 'string|nullable',
            'url_instagram' => 'string|nullable',
            'url_twitter' => 'string|nullable',
            'profile' => 'string|nullable',
            'business_id' => 'nullable|integer',
            'classification_id' => 'nullable|integer',
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Put(
     *     path="/api/seller/profile/edit/{id}",
     *     summary="Edit seller",
     *     tags={"Seller"},
     *      security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of account need to input info",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"email","phone","type_of_industry","formality"},
     *                          @OA\Property(
     *                             property="account_name",
     *                             example="name",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="business_id",
     *                             example="1",
     *                             type="integer",
     *                         ),
     *                          @OA\Property(
     *                             property="classification_id",
     *                             example="1",
     *                             type="integer",
     *                         ),
     *                          @OA\Property(
     *                             property="url_official",
     *                             example="",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="url_facebook",
     *                             example="",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="url_instagram",
     *                             example="",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="url_twitter",
     *                             example="",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="profile",
     *                             example="",
     *                             type="string",
     *                         ),
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
    public function update(Request $request, $id)
    {
        try {
            
            $rules = $this->rules();
            $validator = Validator::make(array_filter($request->all()), $rules);
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            DB::beginTransaction();

            $url_official = $request->url_official;
            $url_facebook = $request->url_facebook;
            $url_twitter = $request->url_twitter;
            $url_instagram = $request->url_instagram;
            // check seller not exist
            $seller = $this->seller->findByAccountId($id);
            
            $user_id = Auth::guard(UserConst::USER_GUARD)->user()->id;

            if ($user_id != $seller->account_id && !isset($seller)) return $this->sendError(__('app.not_have_permission'));
            
            $seller_name = $seller->account_name;

            //create url_officials
            if (!isset($seller['url_official_id'])) {
                $urlOfficial = new UrlOfficial();
                $urlOfficial["url_official"] = $url_official;
                $urlOfficial["url_facebook"] = $url_facebook;
                $urlOfficial["url_instagram"] = $url_instagram;
                $urlOfficial["url_twitter"] = $url_twitter;
                $urlOfficial->save();
            } else {
                //update url_officials
                $urlOfficial = $this->urlOfficial->find($seller['url_official_id']);
                if (isset($url_official)) $urlOfficial["url_official"] = $url_official;
                if (isset($url_facebook)) $urlOfficial["url_facebook"] = $url_facebook;
                if (isset($url_instagram)) $urlOfficial["url_instagram"] = $url_instagram;
                if (isset($url_twitter)) $urlOfficial["url_twitter"] = $url_twitter;
                $urlOfficial->save();
            }

            //set data update seller
            $seller['url_official_id'] = $urlOfficial['id'];
            $seller['business_id'] = isset($request->business_id) ? $request->business_id : $seller['business_id'];
            $seller['profile_text_sell'] = isset($request->profile) ? $request->profile : "";
            $seller['last_name'] = isset($request->last_name) ? $request->last_name : $seller['last_name'];
            $seller['first_name'] = isset($request->first_name) ? $request->first_name : $seller['first_name'];
            $seller['account_name'] = isset($request->account_name) ? $request->account_name : $seller['account_name'];
            $seller['gender'] = isset($request->gender) ? $request->gender : $seller['gender'];
            
            //update seller
            $this->seller->updateSeller($id, $seller->toArray());

            $account = $this->account->find($seller['account_id']);
            if (!isset($account)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));

            $account['email'] = isset($request->email) ? $request->email : $account['email'];
            $account['business_type'] = isset($request->business_type) ? $request->business_type : $account['business_type'];
            $account['classification_id'] = isset($request->classification_id) ? $request->classification_id : $account['classification_id'];

            $this->account->updateAccount($id, $account->toArray());

            if ($request->account_name != $seller_name) {
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
            return $this->sendSuccess(__('app.update_account_success'));
        } catch (Exception $e) {
            DB::rollBack();
            $this->log("updateSeller", null, ["seller_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/account/{id}",
     *     summary="Get account seller",
     *     tags={"Seller"},
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
    public function getAccount($id, Request $request)
    {
        try {

            $profile = $this->account->findAccountSeller($id);

            $user_id = Auth::guard(UserConst::USER_GUARD)->user()->id;

            if ($user_id != $profile->id && !isset($seller)) return $this->sendError(__('app.not_have_permission'));
            
            if (isset($profile['sellers'])) {
                if($profile['sellers']['url_official_id']){
                    $url_officials = $this->urlOfficial->find($profile['sellers']['url_official_id']);
                    $profile['sellers']['url_official'] = $url_officials['url_official'];
                    $profile['sellers']['url_facebook'] = $url_officials['url_facebook'];
                    $profile['sellers']['url_instagram'] = $url_officials['url_instagram'];
                    $profile['sellers']['url_twitter'] = $url_officials['url_twitter'];
                }else{
                    $profile['sellers']['url_official'] = '';
                    $profile['sellers']['url_facebook'] = '';
                    $profile['sellers']['url_instagram'] = '';
                    $profile['sellers']['url_twitter'] = '';
                }

                $list_threads = $this->message_thread->getAllThreadSeller($id, $request);
                $count_unread_message = 0;

                foreach ($list_threads as $thread) {
                    $message_unread = 0;
                    if ($thread->buyer_id && $thread->buyers) {
                        $message_unread = $this->message->countMessageUnreadUser($id, $thread->id);
                        $thread->message_unread = $message_unread;
                    } elseif ($thread->admin_id && $thread->admins && !$thread->buyer_id) {
                        $message_unread = $this->message->countMessageUnreadAdmin($thread->id);
                        $thread->message_unread = $message_unread;
                    }
                    $count_unread_message = $count_unread_message + $thread->message_unread;
                }
                $profile['count_unread_message'] = $count_unread_message;
            }

            if ($profile) {
                return $this->sendSuccessResponse($profile);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller')]));
            }
        } catch (Exception $e) {
            $this->log("getAccountSeller", null, ["seller_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/seller/profile/avatar/edit",
     *     summary="Upload avatar seller",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="ID of seller need to display",
     *                     property="seller_id",
     *                     type="integer",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     description="File to upload",
     *                     property="file",
     *                     type="file",
     *                     format="file",
     *                 ),
     *                 required={"file", "seller_id"}
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
    public function uploadAvatarProfile(Request $request)
    {
        try {
            $rules = [
                'file' => 'required|nullable|mimes:jpeg,jpg,png|max:5120',
                'seller_id' => 'required|integer'
            ];
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            $seller = $this->seller->findByAccountId($request->seller_id);

            $user_id = Auth::guard(UserConst::USER_GUARD)->user()->id;

            if ($user_id != $seller->account_id && !isset($seller)) return $this->sendError(__('app.not_have_permission'));
            
            $old_image = $seller['profile_image_url_sell'];
            
            if (!$request->hasFile('file')) {
                return $this->sendError(__('app.media_type'));
            }

            $file = $request->file;
            $mimeType = $file->getMimeType();
            if (0 !== strpos($mimeType, 'image')) {
                return $this->sendError(__('app.media_type'));
            }

            $extension = $file->getClientOriginalExtension();
            $fileName = date('mdHis') . uniqid('_') . '.' . $extension;

            //update field profile_image_url in seller
            $file->move(public_path('storage/avatar'), $fileName);
            $seller['profile_image_url_sell'] = $fileName;
            $this->seller->updateSeller($request->seller_id, $seller->toArray());

            //delete image old
            Storage::disk('public')->delete($old_image);
            return $this->sendSuccess(__('app.upload_success',['attribute'=>__('app.images')]));
        } catch (Exception $e) {
            $this->log("uploadAvatarProfileSeller", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/profile-public/{id}",
     *     summary="Get account seller",
     *     tags={"Seller"},
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
    public function getProfile($id, Request $request)
    {
        try {
            $profile = $this->account->findAccountSeller($id);

            if (isset($profile['sellers'])) {
                if($profile['sellers']['url_official_id']){
                    $url_officials = $this->urlOfficial->find($profile['sellers']['url_official_id']);
                    $profile['sellers']['url_official'] = $url_officials['url_official'];
                    $profile['sellers']['url_facebook'] = $url_officials['url_facebook'];
                    $profile['sellers']['url_instagram'] = $url_officials['url_instagram'];
                    $profile['sellers']['url_twitter'] = $url_officials['url_twitter'];
                }else{
                    $profile['sellers']['url_official'] = '';
                    $profile['sellers']['url_facebook'] = '';
                    $profile['sellers']['url_instagram'] = '';
                    $profile['sellers']['url_twitter'] = '';
                }
            }

            if ($profile) {
                return $this->sendSuccessResponse($profile);
            } else {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.seller')]));
            }
        } catch (Exception $e) {
            $this->log("getAccountSeller", null, ["seller_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/seller/support",
     *     summary="Send request support to admin",
     *     tags={"Seller"},
     *     security={ {"bearer": {}} },
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
    public function requestSupport()
    {
        try {
            $user = Auth::guard(UserConst::USER_GUARD)->user();

            $account = $this->account->find($user->id);
            if (!$account) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));

            $title = 'サポート依頼がありました。（仮）';

            $info = $this->seller->getProfileSeller($account->id);

            $data = [
                'seller_name' => $info['account_name'],
            ];

            $admins = $this->admin->getAdmin();
            if (isset($admins)) {
                $toEmail = [];
                foreach ($admins as $admin) {
                    $admin_email = new To($admin->email);
                    array_push($toEmail, $admin_email);
                }
                array_push($toEmail, UserConst::CHEAT_TEST_EMAIL);
                if (sizeof($toEmail) > 0)
                    $this->sendEmail('email.email-request-support-to-admin', $toEmail, $data, $title);
            }
            
            return $this->sendSuccess(__('app.request_support_success'));
        } catch (Exception $e) {
            $this->log("requestSupport", null, null, $e->getFile() . " - " . $e->getLine() . " - " . $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
