<?php

namespace App\Http\Controllers\Api;

use App\Constants\UserConst;
use App\Http\Controllers\Api\BaseController;
use App\Models\Account;
use App\Models\Buyer;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\UrlOfficial;
use App\Models\VerifyAccountIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BuyerController extends BaseController
{
    protected $buyer;
    protected $account;
    protected $urlOfficial;
    protected $verifyAccountIdentity;
    protected $message_thread;
    protected $message;

    public function __construct(
        Buyer $buyer,
        Account $account,
        UrlOfficial $urlOfficial,
        VerifyAccountIdentity $verifyAccountIdentity,
        MessageThread $message_thread,
        Message $message
    ) {
        $this->buyer = $buyer;
        $this->account = $account;
        $this->urlOfficial = $urlOfficial;
        $this->verifyAccountIdentity = $verifyAccountIdentity;
        $this->message_thread = $message_thread;
        $this->message = $message;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array

     */

    public function rules()
    {
        return [
            'account_name' => 'nullable|string',
            'gender' => 'integer|nullable',
            'profile' => 'string|nullable',
            'classification_id' => 'integer|nullable',
            'url_facebook' => 'string|nullable',
            'url_instagram' => 'string|nullable',
            'url_twitter' => 'string|nullable',
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Put(
     *     path="/api/buyer/profile/edit/{id}",
     *     summary="Edit buyer",
     *     tags={"Buyer"},
     *      security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of buyer need to input info",
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
     *                        required={"business_format","account_name"},
     *                         @OA\Property(
     *                             property="account_name",
     *                             example="name",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="profile",
     *                             example="",
     *                             type="string",
     *                         ),
     *                          @OA\Property(
     *                             property="gender",
     *                             example="1",
     *                             type="integer",
     *                         ),
     *                          @OA\Property(
     *                             property="classification_id",
     *                             example="1",
     *                             type="integer",
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
            $url_facebook = $request->url_facebook;
            $url_twitter = $request->url_twitter;
            $url_instagram = $request->url_instagram; 
            DB::beginTransaction();
            
            // check buyer not exist
            $buyer = $this->buyer->findByAccountId($id);

            $use_id = Auth::guard(UserConst::USER_GUARD)->user()->id;
            
            if ($use_id != $buyer->account_id && !isset($buyer)) return $this->sendError(__('app.not_have_permission'));

            $buyer_name =  $buyer->account_name;

            //create url_officials
            if (!isset($buyer['url_official_id'])) {
                $urlOfficial = new UrlOfficial();
                $urlOfficial["url_facebook"] = $url_facebook;
                $urlOfficial["url_instagram"] = $url_instagram;
                $urlOfficial["url_twitter"] = $url_twitter;
                $urlOfficial->save();
            } else {
                //update url_officials
                $urlOfficial = $this->urlOfficial->find($buyer['url_official_id']);
                if (isset($url_official)) $urlOfficial["url_official"] = $url_official;
                if (isset($url_facebook)) $urlOfficial["url_facebook"] = $url_facebook;
                if (isset($url_instagram)) $urlOfficial["url_instagram"] = $url_instagram;
                if (isset($url_twitter)) $urlOfficial["url_twitter"] = $url_twitter;
                $urlOfficial->save();
            }
            //set data update buyer
            $buyer['updated_at'] = Carbon::now()->toDateTimeString();
            $buyer['url_official_id'] = $urlOfficial['id'];
            $buyer['profile_text_buy'] = isset($request->profile) ? $request->profile : "";
            $buyer['last_name'] = isset($request->last_name) ? $request->last_name : $buyer['last_name'];
            $buyer['first_name'] = isset($request->first_name) ? $request->first_name : $buyer['first_name'];
            $buyer['account_name'] = isset($request->account_name) ? $request->account_name : $buyer['account_name'];
            $buyer['gender'] = isset($request->gender) ? $request->gender : $buyer['gender'];
            
            //update buyer
            $this->buyer->updateBuyer($id, $buyer->toArray());

            $account = $this->account->find($buyer['account_id']);
            if (!isset($account)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.account')]));

            $account['email'] = isset($request->email) ? $request->email : $account['email'];
            $account['business_type'] = isset($request->business_type) ? $request->business_type : $account['business_type'];
            $account['classification_id'] = isset($request->classification_id) ? $request->classification_id : $account['classification_id'];
            
            $this->account->updateAccount($id, $account->toArray());

            if ($request->account_name != $buyer_name) {
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
            error_log($e);
            DB::rollBack();
            $this->log("updateBuyer", null, ["buyer_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/buyer/account/{id}",
     *     summary="Get account buyer",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
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
    public function getAccount($id, Request $req)
    {
        try {
            // check buyer not exist
            $profile = $this->account->findByAccountBuyer($id);

            $use_id = Auth::guard(UserConst::USER_GUARD)->user()->id;
            
            if ($use_id != $profile->id && !isset($profile)) return $this->sendError(__('app.not_have_permission'));

            if (isset($profile['buyers'])) {
                if($profile['buyers']['url_official_id']){
                    $url_officials = $this->urlOfficial->find($profile['buyers']['url_official_id']);
                    $profile['buyers']['url_official'] = $url_officials['url_official'];
                    $profile['buyers']['url_facebook'] = $url_officials['url_facebook'];
                    $profile['buyers']['url_instagram'] = $url_officials['url_instagram'];
                    $profile['buyers']['url_twitter'] = $url_officials['url_twitter'];
                }else{
                    $profile['buyers']['url_official'] = '';
                    $profile['buyers']['url_facebook'] = '';
                    $profile['buyers']['url_instagram'] = '';
                    $profile['buyers']['url_twitter'] = '';
                }
                $list_threads = $this->message_thread->getAllThreadBuyer($id, $req);
                $count_unread_message = 0;
                foreach ($list_threads as $thread) {
                    $message_unread = 0;
                    if ($thread->seller_id && $thread->sellers) {
                        $message_unread = $this->message->countMessageUnreadUser($id, $thread->id);
                        $thread->message_unread = $message_unread;
                    } elseif ($thread->admin_id && $thread->admins && !$thread->seller_id) {
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
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
            }
        } catch (Exception $e) {
            $this->log("getAccountBuyer", null, ["buyer_id" => $id], $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/buyer/profile/avatar/edit",
     *     summary="Upload avatar buyer",
     *     tags={"Buyer"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="ID of buyer need to display",
     *                     property="buyer_id",
     *                     type="integer",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     description="File to upload",
     *                     property="file",
     *                     type="file",
     *                     format="file",
     *                 ),
     *                 required={"file", "buyer_id"}
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
                'buyer_id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();
            if ($errors->first()) return $this->sendError($errors->first());

            $buyer = $this->buyer->findByAccountId($request->buyer_id);

            $use_id = Auth::guard(UserConst::USER_GUARD)->user()->id;
            
            if ($use_id != $buyer->account_id && !isset($profile)) return $this->sendError(__('app.not_have_permission'));

            $old_image = $buyer['profile_image_url_buy'];
            // check buyer not exist
            if (!isset($buyer)) return $this->sendError(__('app.not_exist', ['attribute' => __('app.buyer')]));
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

            //update field profile_image_url_buy in buyer
            $file->move(public_path('storage/avatar'), $fileName);
            $buyer['profile_image_url_buy'] = $fileName;
            $this->buyer->updateBuyer($request->buyer_id, $buyer->toArray());
            //delete image old
            Storage::disk('public')->delete($old_image);
            return $this->sendSuccess(__('app.upload_success',['attribute'=>__('app.images')]));
        } catch (Exception $e) {
            $this->log("uploadAvatarProfileBuyer", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
