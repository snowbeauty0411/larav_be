<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\MessageThread;
use App\Constants\UserConst;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use App\Constants\MessageConst;
use App\Models\Buyer;
use App\Models\Seller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MessageManagementController extends BaseController
{
    protected $message_thread;
    protected $account;
    protected $admin;
    protected $message;
    protected $buyer;
    protected $seller;
    public function __construct(
        MessageThread $message_thread,
        Account $account,
        Admin $admin,
        Message $message,
        Buyer $buyer,
        Seller $seller
    ) {
        $this->message_thread = $message_thread;
        $this->account = $account;
        $this->admin = $admin;
        $this->message = $message;
        $this->buyer = $buyer;
        $this->seller = $seller;
    }

    public function filterRules()
    {
        return [
            'keyword' => 'string|nullable'
        ];
    }

    public function createMessageRules()
    {
        return [
            'message_thread_id' => 'required|integer',
            'message_content' => 'string|nullable',
            'file' => 'max:10240|nullable'
        ];
    }

    public function customMessage()
    {
        return [
            'message_thread_id.required' => __('validation.required', ['attribute' => __('app.thread_id')]),
            'message_thread_id.integer' => __('validation.integer', ['attribute' => __('app.thread_id')]),
            'message_content.string' => __('validation.string', ['attribute' => __('app.message')]),
            'keyword.string' => __('validation.string', ['attribute' => __('app.keyword')]),
            'seller_id.required' => __('validation.required', ['attribute' => __('app.seller_id')]),
            'seller_id.integer' => __('validation.integer', ['attribute' => __('app.seller_id')]),
            'buyer_id.integer' => __('validation.integer', ['attribute' => __('app.buyer_id')]),
            'buyer_id.required' => __('validation.required', ['attribute' => __('app.buyer_id')]),
            'admin_id.integer' => __('validation.integer', ['attribute' => __('app.admin_id')]),
            'admin_id.required' => __('validation.required', ['attribute' => __('app.admin_id')]),
            'receiver_id.required' => __('validation.required', ['attribute' => __('app.admin_id')]),
            'login_type.required' => __('validation.required', ['attribute' => __('app.login_type')]),
            'login_type.integer' => __('validation.integer', ['attribute' => __('app.login_type')]),
        ];
    }


    //User list thread
    /**
     *     @OA\Post(
     *     path="/api/chat/user/thread/list",
     *     summary="List thread of user",
     *     tags={"Chat By Role Buyer Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="keyword",
     *                             type="string",
     *                             example=""
     *                         ),
     *                          @OA\Property(
     *                             description="type login of user 1: buyer 2 :seller",
     *                             property="login_type",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                             @OA\Property(
     *                             description="",
     *                             property="per_page",
     *                             type="integer",
     *                             example=1
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="get all thread of user successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function listThreadUser(Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;
            if (isset($request->login_type) && $request->login_type == 1) {
                $list_threads = $this->message_thread->getAllThreadBuyer($id, $request);
            } elseif (isset($request->login_type) && $request->login_type == 2) {
                $list_threads = $this->message_thread->getAllThreadSeller($id, $request);
            } else {
                return $this->sendError(__('app.not_have_permission'));
            }

            $rules = [
                'login_type' => 'required|integer',
                'keyword' => 'string|nullable'
            ];

            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            if ($request->login_type == 1) {
                foreach ($list_threads as $thread) {
                    $message_unread = 0;
                    $last_message = null;
                    $last_message_at = null;
                    $partner_avatar_url = null;
                    if ($thread->seller_id && $thread->sellers) {
                        $thread->partner_name = $thread->sellers->account_name;
                        if ($thread->sellers->profile_image_url_sell != null) {
                            $partner_avatar_url = config('app.app_resource_path') . 'avatar/' .  $thread->sellers->profile_image_url_sell;
                        }
                        $thread->partner_avatar_url = $partner_avatar_url;
                        $message_unread = $this->message->countMessageUnreadUser($id, $thread->id);
                        $thread->message_unread = $message_unread;
                        $last_message = $this->message->getLastMessageUser($id, $thread->id);
                        if ($last_message) {
                            $last_message_at = $last_message->created_at;
                        }
                        $thread->last_message_at = $last_message_at;
                    } elseif ($thread->admin_id && $thread->admins && !$thread->seller_id) {
                        $thread->partner_name = $thread->admins->name;
                        $thread->partner_avatar_url = null;
                        $message_unread = $this->message->countMessageUnreadAdmin($thread->id);
                        $thread->message_unread = $message_unread;
                        $last_message = $this->message->getLastMessageAdmin($thread->id);

                        if ($last_message) {
                            $last_message_at = $last_message->created_at;
                        }
                        $thread->last_message_at = $last_message_at;
                    }
                }
            } elseif ($request->login_type == 2) {
                foreach ($list_threads as $thread) {
                    $message_unread = 0;
                    $last_message = null;
                    $last_message_at = null;
                    $partner_avatar_url = null;
                    if ($thread->buyer_id && $thread->buyers) {
                        $thread->partner_name = $thread->buyers->account_name;
                        if ($thread->buyers->profile_image_url_buy != null) {
                            $partner_avatar_url = config('app.app_resource_path') . 'avatar/' .  $thread->buyers->profile_image_url_buy;
                        }
                        $thread->partner_avatar_url = $partner_avatar_url;
                        $message_unread = $this->message->countMessageUnreadUser($id, $thread->id);
                        $thread->message_unread = $message_unread;
                        $last_message = $this->message->getLastMessageUser($id, $thread->id);

                        if ($last_message) {
                            $last_message_at = $last_message->created_at;
                        }
                        $thread->last_message_at = $last_message_at;
                    } elseif ($thread->admin_id && $thread->admins && !$thread->buyer_id) {
                        $thread->partner_name = $thread->admins->name;
                        $thread->partner_avatar_url = null;
                        $message_unread = $this->message->countMessageUnreadAdmin($thread->id);
                        $thread->message_unread = $message_unread;
                        $last_message = $this->message->getLastMessageAdmin($thread->id);

                        if ($last_message) {
                            $last_message_at = $last_message->created_at;
                        }
                        $thread->last_message_at = $last_message_at;
                    }
                }
            }

            return $this->sendSuccessResponse($list_threads);
        } catch (Exception $e) {
            error_log($e);
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/chat/list-thread",
     *     summary="List thread of user",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="page",
     *         in="path",
     *         name="page",
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="keyword",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             description="",
     *                             property="per_page",
     *                             type="integer",
     *                             example=1
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="get all thread of admin successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function listThreadByAdmin(Request $request)
    {
        try {
            $id = auth(UserConst::ADMIN_GUARD)->user()->id;
            $list_thread = $this->message_thread->getAllThreadByAdmin($id, $request);
            foreach ($list_thread as $thread) {
                $avatar_url = null;
                if ($thread->buyer_id && $thread->buyers) {
                    $thread->partner_id = $thread->buyer_id;
                    $thread->partner_name = $thread->buyers->account_name;
                    if ($thread->buyers->profile_image_url_buy != null) {
                        $avatar_url = config('app.app_resource_path') . 'avatar/' . $thread->buyers->profile_image_url_buy;
                    }
                    $thread->partner_avatar_url = $avatar_url;
                } elseif ($thread->seller_id && $thread->sellers) {
                    $thread->partner_id = $thread->seller_id;
                    $thread->partner_name = $thread->sellers->account_name;
                    if ($thread->sellers->profile_image_url_sell != null) {
                        $avatar_url = config('app.app_resource_path') . 'avatar/' . $thread->sellers->profile_image_url_sell;
                    }
                    $thread->partner_avatar_url = $avatar_url;
                }
                $thread->message_unread = $this->message->countMessageUnreadForAdmin($thread->id);
                $thread->last_message = $this->message->getLastMessageForAdmin($thread->id);
            }
            return $this->sendSuccessResponse($list_thread);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/chat/user/create/thread",
     *     summary="Create thread to user",
     *     tags={"Chat By Role Buyer Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="receiver_id",
     *                             type="integer",
     *                             example=""
     *                         ),
     *                          @OA\Property(
     *                             property="login_type",
     *                             type="integer",
     *                             example=1
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="create thread chat successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function createThreadToUserByUser(Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;
            $rules = [
                'login_type' => 'required|integer',
                'receiver_id' => 'required|integer'
            ];

            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            if ($request->login_type == 1) {
                $check_exist = $this->message_thread->checkExistByUser($id, $request->receiver_id);
                if ($check_exist) {
                    return $this->sendSuccessResponse($check_exist->id);
                } else {
                    $thread = $this->message_thread->create([
                        'buyer_id' => $id,
                        'seller_id' => $request->receiver_id
                    ]);
                }
            } elseif ($request->login_type == 2) {
                $check_exist = $this->message_thread->checkExistByUser($request->receiver_id, $id);
                if ($check_exist) {
                    return $this->sendSuccessResponse($check_exist->id);
                } else {
                    $thread = $this->message_thread->create([
                        'seller_id' => $id,
                        'buyer_id' => $request->receiver_id
                    ]);
                }
            }
            if ($thread) {
                return $this->sendSuccessResponse($thread->id);
            }
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createThreadToAdminByUser(Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;
            $admin = $this->admin->adminInfo();
            $admin_id = $admin->id;
            $rules = [
                'login_type' => 'required|integer'
            ];
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            if ($request->login_type == 1) {
                $check_exist = $this->message_thread->checkExistWithAdminByBuyer($admin_id, $id);
                if ($check_exist) {
                    return $this->sendSuccess($check_exist->id);
                } else {
                    $this->message_thread->create(
                        [
                            'buyer_id' => $id,
                            'admin_id' => $admin_id
                        ]
                    );
                }
            } elseif ($request->login_type == 2) {
                $check_exist = $this->message_thread->checkExistWithAdminBySeller($admin_id, $id);
                if ($check_exist) {
                    return $this->sendSuccess($check_exist->id);
                } else {
                    $this->message_thread->create(
                        [
                            'seller_id' => $id,
                            'admin_id' => $admin_id
                        ]
                    );
                }
            }
            return $this->sendSuccessResponse(
                __('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.message_thread')])
            );
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     *     @OA\Post(
     *     path="/api/admin/chat/thread/to-buyer/create",
     *     summary="Create thread by Admin to buyer",
     *     tags={"Chat By Role Admin"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="buyer_id",
     *                             type="integer",
     *                             example=""
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="create thread chat successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function createThreadToBuyerByAdmin(Request $request)
    {
        try {
            $id = auth(UserConst::ADMIN_GUARD)->user()->id;
            $rules = ['buyer_id' => 'required|integer'];
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $check_exist = $this->message_thread->checkExistWithAdminByBuyer($id, $request->buyer_id);
            if ($check_exist) {
                return $this->sendSuccess($check_exist->id);
            } else {
                $thread = $this->message_thread->create([
                    'admin_id' => $id,
                    'buyer_id' => $request->buyer_id
                ]);
            }
            if ($thread) {
                return $this->sendSuccessResponse($thread->id);
            }
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/chat/thread/to-seller/create",
     *     summary="Create thread by Admin to seller",
     *     tags={"Chat By Role Admin"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="seller_id",
     *                             type="integer",
     *                             example=""
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="create thread chat successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function createThreadToSellerByAdmin(Request $request)
    {
        try {
            $id = auth(UserConst::ADMIN_GUARD)->user()->id;
            $rules = ['seller_id' => 'required|integer'];
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $check_exist = $this->message_thread->checkExistWithAdminBySeller($id, $request->seller_id);
            if ($check_exist) {
                return $this->sendSuccess($check_exist->id);
            } else {
                $thread = $this->message_thread->create([
                    'admin_id' => $id,
                    'seller_id' => $request->seller_id
                ]);
            }
            if ($thread) {
                return $this->sendSuccessResponse($thread->id);
            }
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     *     @OA\Post(
     *     path="/api/chat/user/thread/index/{thread_id}",
     *     summary="thread index",
     *     tags={"Chat By Role Buyer Seller"},
     *     security={ {"bearer": {}} },
     *     @OA\Parameter(
     *         description="ID of thread",
     *         in="path",
     *         name="thread_id",
     *         required=true,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="login_type",
     *                             type="string",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="keyword",
     *                             type="string",
     *                             example=""
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function indexUser($thread_id, Request $request)
    {
        try {
            $id = auth(UserConst::USER_GUARD)->user()->id;
            $rules = [
                'login_type' => 'required|integer',
                'keyword' => 'string|nullable'
            ];
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }
            $data = array();

            $thread = $this->message_thread->where('id', $thread_id)->first();

            if ($request->login_type == 1) {
                if ($thread->buyer_id != $id) return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.message_thread')]));
            } else {
                if ($thread->seller_id != $id) return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.message_thread')]));
            }

            $avatar_url = null;
            $partner_id = null;
            $partner_name = null;
            if ($request->login_type == 1) {

                if ($thread->seller_id != null) {
                    $partner_id = $thread->seller_id;
                    $seller = $this->seller->where('account_id', $thread->seller_id)->first();
                    $partner_name = $seller->account_name;
                    if ($seller->profile_image_url_sell != null) {
                        $avatar_url = config('app.app_resource_path') . 'avatar/' . $seller->profile_image_url_sell;
                    }
                } else {
                    $partner_id = $thread->admin_id;
                    $admin = $this->admin->where('id', $thread->admin_id)->first();
                    $partner_name = $admin->name;
                }
            } elseif ($request->login_type == 2) {
                if ($thread->buyer_id != null) {
                    $partner_id = $thread->buyer_id;
                    $buyer = $this->buyer->where('account_id', $thread->buyer_id)->first();
                    $partner_name = $buyer->account_name;
                    if ($buyer->profile_image_url_buy != null) {
                        $avatar_url = config('app.app_resource_path') . 'avatar/' . $buyer->profile_image_url_buy;
                    }
                } else {
                    $partner_id = $thread->admin_id;
                    $admin = $this->admin->where('id', $thread->admin_id)->first();
                    $partner_name = $admin->name;
                }
            }

            $messages = $this->message->getAllMessageByThread($thread_id, $request);
            $data['partner_info']['partner_id'] = $partner_id;
            $data['partner_info']['partner_name'] = $partner_name;
            $data['partner_info']['partner_avatar_img_url'] = $avatar_url;
            $data['messages'] = $messages;
            $this->message->readMarkUser($thread->id, $id);
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/chat/thread/index",
     *     summary="thread index",
     *     tags={"Admin"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="thread_id",
     *                             type="string",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="keyword",
     *                             type="string",
     *                             example=""
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function indexAdmin(Request $request)
    {
        try {
            $id = auth(UserConst::ADMIN_GUARD)->user()->id;
            $thread = $this->message_thread->where('id', $request->thread_id)->where('admin_id', $id)->first();
            if (!$thread) return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.message_thread')]));

            $avatar_url = null;
            $partner_id = null;
            $partner_name = null;
            if ($thread->buyer_id != null) {
                $partner_id = $thread->buyer_id;
                $buyer = $this->buyer->where('account_id', $thread->buyer_id)->first();
                $partner_name = $buyer->account_name;
                if ($buyer->profile_image_url_buy != null) {
                    $avatar_url = config('app.app_resource_path') . 'avatar/' . $buyer->profile_image_url_buy;
                }
            } else {
                $partner_id = $thread->buyer_id;
                $seller = $this->seller->where('account_id', $thread->seller_id)->first();
                $partner_name = $seller->account_name;
                if ($seller->profile_image_url_sell != null) {
                    $avatar_url = config('app.app_resource_path') . 'avatar/' . $seller->profile_image_url_sell;
                }
            }
            $messages = $this->message->getAllMessageByThread($request->thread_id, $request);
            $data = array();
            $data['partner_info']['partner_id'] = $partner_id;
            $data['partner_info']['partner_name'] = $partner_name;
            $data['partner_info']['partner_avatar_img_url'] = $avatar_url;
            $data['messages'] = $messages;
            $this->message->readMarkAdminIndex($request->thread_id);
            return $this->sendSuccessResponse($data);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *     @OA\Post(
     *     path="/api/chat/message-create",
     *     summary="create message",
     *     tags={"Chat By Role Buyer Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                  property="message_thread_id",
     *                  type="integer",
     *                  example=1
     *               ),
     *               @OA\Property(
     *                  property="message_content",
     *                       type="string",
     *                       example="Hello"
     *               ),
     *               @OA\Property(
     *                  property="file",
     *                       type="string",
     *                       format="binary",
     *               ),
     *
     *           ),
     *       )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="create message  successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function createMessage(Request $request)
    {
        try {
            $isAdmin = false;
            if (auth(UserConst::ADMIN_GUARD)->user()) {
                $id = auth(UserConst::ADMIN_GUARD)->user()->id;
                $isAdmin = true;
            } else $id = auth(UserConst::USER_GUARD)->user()->id;
            $rules = $this->createMessageRules();
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $check_permission = $this->message_thread->where('id', $request->message_thread_id)->first();
            if ($isAdmin) {
                if ($check_permission->admin_id != $id) {
                    return $this->sendError(__('app.not_have_permission'));
                }
            } else {
                if ($check_permission->seller_id != $id && $check_permission->buyer_id != $id) {
                    return $this->sendError(__('app.not_have_permission'));
                }
            }


            if ($isAdmin) {
                $request->request->add(['admin_id' => $id]);
            } else {
                $request->request->add(['from_id' => $id]);
            }
            $request->request->add(['created_at' => Carbon::now()->toDateTimeString()]);
            $check_thread_exist = $this->message_thread->where('id', $request->message_thread_id)->first();
            if (!$check_thread_exist) return $this->sendError(__('app.not_exist', ['attribute' => __('app.message_thread')]));

            if (isset($request->file)) {
                $file = $request->file('file');
                $time_upload = microtime(true);
                $file_type = $file->getClientOriginalExtension();
                $unique_str = uniqid("", true);
                $file_name = $time_upload . '-' . $unique_str . '.' . $file_type;
                $file->move(public_path('storage/chat/' . $request->message_thread_id), $file_name);
                $file_path = 'chat/' . $request->message_thread_id . '/' . $file_name;
                $request->request->add([
                    "file_name" => $file_name,
                    "file_path" => $file_path,
                    "file_type" => $file_type
                ]);
            }
            $this->message->create($request->all());
            //send mail
            $thread = $this->message_thread->where('id', $request->message_thread_id)->first();
            $thread->update([
                'updated_at' => now(),
            ]);
            if ($isAdmin && $thread->admin_id != null) {
                if ($thread->seller_id != null) {
                    $account = $this->account->accountInfo($thread->seller_id);
                    $receiver = $account->sellers; 
                    if ($receiver) {
                        $receiver_name = $receiver->account_name;
                    }
                    $email = $account->email;
                } elseif ($thread->buyer_id != null) {
                    $account = $this->account->accountInfo($thread->buyer_id);
                    $receiver = $account->buyers; 
                    if ($receiver) {
                        $receiver_name = $receiver->account_name;
                    }
                    $email = $receiver->email;
                }
                if ($account->message_mail_flg == 1) {
                    $title = "【subsQ】システムのメッセージ受信通知";
                    $data = [
                        'receiver_name' => $receiver_name,
                        'sender_name' => 'システム管理者',
                        'chat_url' => config('app.url') . MessageConst::CHAT_URL . $request->message_thread_id
                    ];
                    $this->sendEmail('email.email-send-message', $email, $data, $title);
                }
            } else {
                if ($thread->admin_id == null) {
                    if ($thread->seller_id == $id) {
                        $receiver_account = $this->account->accountInfo($thread->buyer_id);
                        $receiver = $receiver_account->buyers;
                        $seller_user = $this->account->accountInfo($thread->seller_id);
                        $sender = $seller_user->sellers;
                        $receiver_email = $receiver_account->email;
                    } elseif ($thread->buyer_id == $id) {
                        $receiver_account = $this->account->accountInfo($thread->seller_id);
                        $receiver = $receiver_account->sellers;
                        $buyer_user = $this->account->accountInfo($thread->buyer_id);
                        $sender = $buyer_user->buyers;
                        $receiver_email = $receiver_account->email;
                    }

                    if ($receiver_account->message_mail_flg == 1) {
                        $title = "【subsQ】". $sender->account_name."さんからのメッセージ受信通知";
                        $data = [
                            'receiver_name' => $receiver->account_name,
                            'sender_name' => $sender->account_name,
                            'chat_url' =>  $thread->seller_id == $id ? config('app.url') . '/seller' . MessageConst::CHAT_URL . $request->message_thread_id : config('app.url') . '/buyer' . MessageConst::CHAT_URL . $request->message_thread_id
                        ];
                        $this->sendEmail('email.email-send-message', $receiver_email, $data, $title);
                    }
                }
            }
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.message')]));
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/api/chat/{thread_id}/{file_name}",
     *     summary="display message file",
     *     tags={"Chat By Role Buyer Seller"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="ID of thread",
     *         in="path",
     *         name="thread_id",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *         type="integer"
     *        )
     *     ),
     * *      @OA\Parameter(
     *         description="file name need display",
     *         in="path",
     *         name="file_name",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *         type="string"
     *        )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get successful message file",
     *     ),
     *     @OA\Response(
     *        response="404",
     *        description="file not found",
     *     )
     * )
     */
    public function getResourceFile($thread_id, $file_name)
    {
        try {
            $messageThread = new MessageThread();
            $data = $messageThread->checkThreadExist($thread_id);
            if (!$data) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.message_thread')]));
            }
            $path = Storage::disk('private')->getDriver()->getAdapter()->getPathPrefix() . 'chat/' . $thread_id . '/' . $file_name;
            return response()->download($path, $file_name);
        } catch (Exception $e) {
            error_log($e);
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
