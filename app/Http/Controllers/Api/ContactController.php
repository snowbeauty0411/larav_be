<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Contact;
use App\Models\Admin;
use SendGrid\Mail\To;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Constants\UserConst;

class ContactController extends BaseController
{
    protected $contact;

    protected $admin;

    public function __construct(Contact $contact, Admin $admin)
    {
        $this->contact = $contact;
        $this->admin = $admin;
    }

    public function createRules()
    {
        return [
            'name' => 'string|required',
            'email' => 'required|string|email|max:256',
            'content' => 'required|string',
        ];
    }

    public function replyRules()
    {
        return [
            'reply_content' => 'required|string'
        ];
    }

    public function customMessage()
    {
        return [
            'email.email' => __('validation.email', ['attribute' => __('app.email')]),
            'email.required' => __('validation.required', ['attribute' => __('app.email')]),
            'email.string' => __('validation.string', ['attribute' => __('app.email')]),
            'email.max' => __('validation.max', ['attribute' => __('app.email')]),
            'name.string' => __('validation.string', ['attribute' => __('app.name')]),
            'name.required' => __('validation.required', ['attribute' => __('app.name')]),
            'content.string' => __('validation.string', ['attribute' => __('app.contact_content')]),
            'content.required' => __('validation.required', ['attribute' => __('app.contact_content')]),
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/contact/create",
     *     summary="Create contact",
     *     tags={"Contact"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="email",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="content",
     *                             type="string",
     *                             example=""
     *                         )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Create contact to system successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function create(Request $request)
    {
        try {
            $rules = $this->createRules();
            $validator = Validator::make($request->all(), $rules, $this->customMessage());
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }
            DB::beginTransaction();
            $this->contact->create($request->all());

            //send mail to user
            $title = __('app.title_mail_contact');


            $data_user = array(
                'name' => $request->name,
                'email' => $request->email,
                'content' => $request->content,
                'APP_URL' => config('app.url'),
            );
            $this->sendEmail('email.email-contact-for-user', $request->email, $data_user, $title);

            //send mail to admin
            $data_admin = array(
                'name' => $request->name,
                'email' => $request->email,
                'content' => $request->content,
                'APP_URL' => config('app.url'),
            );

            //get all admin
            $admins = $this->admin->getAdmin();
            if (isset($admins)) {
                $toEmail = [];
                foreach ($admins as $admin) {
                    $admin_email = new To($admin->email);
                    array_push($toEmail, $admin_email);
                }
                array_push($toEmail, UserConst::CHEAT_TEST_EMAIL);
                if (sizeof($toEmail) > 0)
                    $this->sendEmail('email.email-contact-for-admin', $toEmail, $data_admin, $title);
            }

            DB::commit();
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.contact')]));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Admin get all contacts.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/admin/contact/list",
     *     summary="Admin list contacts",
     *     tags={"Contact"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="",
     *         in="query",
     *         name="page",
     *         required=false,
     *         example=10,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="email",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="sort",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             description="1:ASC 2:DESC",
     *                             property="sort_type",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             description="Number record display on current page",
     *                             property="per_page",
     *                             type="integer",
     *                             example=10
     *                         )
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Get all contacts successful",
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
    public function listContactAdmin(Request $request)
    {
        try {
            $contacts = $this->contact->getAllContactByAdmin($request);
            return $this->sendSuccessResponse($contacts);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Admin get detail contact by id.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Get(
     *     path="/api/admin/contact/detail/{id}",
     *     summary="Admin detail contact",
     *     tags={"Contact"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="id contact need display",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *     @OA\Response(
     *        response="200",
     *        description="Get detail contact successful",
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
    public function detail($id)
    {
        try {
            $contact = $this->contact->findById($id);
            if (!$contact) {
                return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.contact')]));
            }
            return $this->sendSuccessResponse($contact);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // /**
    //  * Admin approve contact by id.
    //  *
    //  * @return \Illuminate\Http\Response
    //  *     @OA\Patch(
    //  *     path="/api/admin/contact/approve/{id}",
    //  *     summary="Admin approve contact",
    //  *     tags={"Contact"},
    //  *     security={ {"bearer": {}} },
    //  *      @OA\Parameter(
    //  *         description="Id contact need approve",
    //  *         in="path",
    //  *         name="id",
    //  *         required=true,
    //  *         example=1,
    //  *         @OA\Schema(
    //  *         type="integer"
    //  *        )
    //  *      ),
    //  *     @OA\Response(
    //  *        response="200",
    //  *        description="Approve contact successful",
    //  *     ),
    //  *     @OA\Response(
    //  *        response="401",
    //  *        description="Bad Request",
    //  *     ),
    //  *    @OA\Response(
    //  *        response="500",
    //  *        description="Internal Server Error",
    //  *     ),
    //  * )
    //  *
    //  */
    // public function approve($id)
    // {
    //     try {
    //         $contact = $this->contact->findById($id);
    //         if (!$contact) {
    //             return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.contact')]));
    //         }

    //         $contact->update([
    //             'status' => 1
    //         ]);

    //         return $this->sendSuccessResponse('問い合わせ承認に成功しました。');
    //     } catch (Exception $e) {
    //         return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }


    /**
     * Admin reply contact.
     *
     * @return \Illuminate\Http\Response
     *     @OA\Post(
     *     path="/api/admin/contact/reply/{id}",
     *     summary="Admin reply contact",
     *     tags={"Contact"},
     *     security={ {"bearer": {}} },
     *      @OA\Parameter(
     *         description="id contact need reply",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example=1,
     *         @OA\Schema(
     *         type="integer"
     *        )
     *      ),
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                @OA\Property(
     *                  property="reply_content",
     *                  type="integer",
     *                  example=""  
     *               ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Reply contact successful",
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
    public function reply($id, Request $request)
    {
        try {
            $rules = $this->replyRules();
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }
            $contact = $this->contact->findById($id);
            if (!$contact) {
                return $this->sendSuccess(__('app.not_exist', ['attribute' => __('app.contact')]));
            }

            $title = '【subsQ】からの顧客返信メールです';

            $data = array(
                'name' => $contact->name,
                'content' => $contact->content,
                'reply_content' => $request->reply_content,
                'APP_URL' => config('app.url'),
            );

            $this->sendEmail('email.email-reply-contact', $contact->email, $data, $title);

            $contact->update([
                'status'=>1
            ]);

            return $this->sendSuccess('返信成功');
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
