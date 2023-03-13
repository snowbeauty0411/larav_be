<?php

namespace App\Http\Controllers\Api;

use App\Constants\SendOtpConst;
use App\Constants\UserConst;
use App\Models\Account;
use App\Models\Buyer;
use App\Models\Seller;
use App\Models\SendOtp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Response;
use Exception;

class RegisteredMemberController extends BaseController
{
    protected $account;
    protected $sendOtp;

    public function __construct(
        Account $account,
        SendOtp $sendOtp
    )
    {
        $this->account = $account;
        $this->sendOtp = $sendOtp;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function checkMailRules()
    {
        return [
            'email' => 'string|email|max:100|required',
        ];
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function registerStep1Rules()
    {
        return [
            'name' => 'string|required|max:26',
            'password' => 'required|string|confirmed|max:30',
            'otp' => 'integer|min:6',
            'token' => 'string',
        ];
    }

    public function registerStep2Rules()
    {
        return [
            'name_last' => 'string|nullable',
            'name_first' => 'string|nullable',
            'name_last_kana' => 'string|nullable',
            'name_first_kana' => 'string|nullable',
            'zipcode' => 'string|max:7|nullable',
            'city' => 'string|nullable',
            'block' => 'string|nullable',
            'building_name' => 'string|nullable',
            'birth_year' => 'string|nullable',
            'sex_id' => 'integer|max:3|nullable',
            'company_name' => 'string|nullable',
            'corporate_number' => 'string|nullable',
            'homebuilder_number' => 'string|nullable',
            'tel_home' => 'string|nullable',
            'tel_mobile' => 'string|nullable',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function customMessage()
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('app.name')]),
            'name.string' => __('validation.required', ['attribute' => __('app.name')]),
            'name.max' => __('validation.required', ['attribute' => __('app.name')]),
            'email.max' => __('validation.max', ['attribute' => __('app.email')]),
            'email.required' => __('validation.required', ['attribute' => __('app.email')]),
            'email.string' => __('validation.string', ['attribute' => __('app.email')]),
            'email.email' => __('validation.email', ['attribute' => __('app.email')]),
            'password.string' => __('validation.string', ['attribute' => __('app.password')]),
            'password.confirmed' => __('validation.confirmed', ['attribute' => __('app.password')]),
            'password.max' => __('validation.max', ['attribute' => __('app.password')])
        ];
    }

    /**
     * Api:check email unique
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     *
     *     @OA\Post(
     *     path="/api/signup/check-email",
     *     summary="Check mail",
     *     tags={"New member"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                  @OA\Property(
     *                   property="email",
     *                   type="string",
     *                   example="example@example.org"
     *                    ),
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
    public function checkEmailRegistrationMember(Request $request)
    {
        try {
            $rules = $this->checkMailRules();
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $check_email_used = $this->account->checkEmailUsed($request->email);

            if ($check_email_used) {
                return $this->sendError('メールアドレスが使用されています。');
            }

            $tokenArr = [];
            $tokenArr[0] = $request->email;

            $email_verify_token_expiration = Carbon::now()->addHours(UserConst::TIME_ACTIVATE)->toDateTimeString();

            $tokenArr[1] = $email_verify_token_expiration;
            $token = Crypt::encrypt($tokenArr);

            $url = config('app.url') . UserConst::ACTIVATION_ACCOUNT_PATH . $token;

            $data = array(
                'url' => $url
            );
            $title = __('app.title_mail_activate_account');
            $toEmail = $request->email;
            $this->sendEmailHaveHtml('email.email-verify-account', $toEmail, $data, $title);
            return $this->sendSuccess($token);
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Api:check email unique
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     *
     *     @OA\Post(
     *     path="/api/mobile/signup/check-email",
     *     summary="Check mail",
     *     tags={"New member"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                  @OA\Property(
     *                   property="email",
     *                   type="string",
     *                   example="example@example.org"
     *                    ),
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
    public function checkEmailRegistrationMemberOtp(Request $request)
    {
        try {
            $rules = $this->checkMailRules();
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $check_email_used = $this->account->checkEmailUsed($request->email);

            if ($check_email_used) {
                return $this->sendError('メールアドレスが使用されています。');
            }

            //find otp in reset_password_otp table
            $sendOtp = $this->sendOtp->getByEmail($request->email, SendOtpConst::REGISTER_ACCOUNT);

            //check OTP valid or expire
            if ($sendOtp && Carbon::now() < Carbon::parse($sendOtp->otp_expire_at)) {
                return $this->sendError(__('app.otp_used'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // create OTP
            $otp = $this->sendOtp->generateOtp();

            //save OTP
            $this->sendOtp->updateOrCreate([
                'email' => $request->email,
                'otp_type' => SendOtpConst::REGISTER_ACCOUNT,
            ], [
                'otp' => $otp,
                'otp_expire_at' => Carbon::now()->addMinutes(1),
            ]);

            $data = array(
                'otp' => $otp
            );
            $title = __('app.title_mail_activate_account');
            $toEmail = $request->email;
            $this->sendEmail('email.email-verify-account-use-otp', $toEmail, $data, $title);
            return $this->sendSuccess(__('app.send_mail_success'));
        } catch (Exception $e) {
            $this->log('checkEmailOtpRegistrationMember', null, null, $e->getMessage() );
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Api:registrationMember
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     *
     *     @OA\Post(
     *     path="/api/signup/create",
     *     summary="register member step 1",
     *     tags={"New member"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="password_confirmation",
     *                             type="string",
     *                             example=""
     *                         ),
     *                          @OA\Property(
     *                             property="token",
     *                             type="string",
     *                             example=""
     *                         ),
     *
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="register successful users",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function registerMemberStep1(Request $request)
    {
        try {
            $rules = $this->registerStep1Rules();
            $data = array_filter($request->all());
            $validator = Validator::make($data, $rules);

            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            DB::beginTransaction();
            
            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($request->token);

            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();

            if ($time_now > $expire_at) {
                return $this->sendError(__('app.active_link_expired'));
            }

            $email = $decryptToken[0];
            $registered_member = $this->account->getInfoAccountByEmail($email);

            if ($registered_member) {
                return $this->sendError(__('app.exist', ['attribute' => __('app.email')]));
            }

            $email_verify_token = Crypt::encryptString($email);

            $request->request->add(['email' => $email]);
            $request->request->add(['email_verify_token' => $email_verify_token]);

            $user = new Account($request->all());
            $user->createAccount();

            $buyer = new Buyer();
            $buyer['account_name'] = $request->name;
            $buyer['account_id'] = $user->id;
            $buyer->save();

            $seller = new Seller();
            $seller['account_name'] = $request->name;
            $seller['account_id'] = $user->id;
            $seller->save();

            $data = array(
                'APP_URL' => config('app.url'),
                'name' => $request->name
            );
            $title = __('app.title_mail_account_registered');
            $this->sendEmailHaveHtml('email.email-registered-account', $email, $data, $title);
            DB::commit();
            $token = auth(UserConst::USER_GUARD)->setTTL(43200)->attempt(['email' => $email, 'password' =>  $request->password]);
            //Password updated, return with success response
            $results = $this->convertDataToken($token, $user->id, $this->account->getTypeAccount($user->id), UserConst::USER_GUARD);
            return $this->sendSuccessResponse($results);
            // return $this->sendSuccessResponse($user['email']);
        } catch (Exception $e) {
            $this->log("registerMemberStep1", null, $request->all(), $e->getMessage());
            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Api:registrationMember
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     *
     *     @OA\Post(
     *     path="/api/mobile/signup/create",
     *     summary="register member step 1",
     *     tags={"New member"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             type="string",
     *                             example=""
     *                         ),
     *                         @OA\Property(
     *                             property="password_confirmation",
     *                             type="string",
     *                             example=""
     *                         ),
     *                          @OA\Property(
     *                             property="otp",
     *                             type="integer",
     *                             example="123456"
     *                         ),
     *
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="register successful users",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     * )
     *
     */
    public function registerMemberStep1Otp(Request $request)
    {
        try {
            $rules = $this->registerStep1Rules();
            $data = array_filter($request->all());
            $validator = Validator::make($data, $rules);

            $errors = $validator->errors();

            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            DB::beginTransaction();

            //find otp in reset_password_otp table
            $sendOtp = $this->sendOtp->getByOTP($request->otp, SendOtpConst::REGISTER_ACCOUNT);

            //check OTP valid or expire
            if (!$sendOtp) {
                return $this->sendError(__('passwords.otp'), Response::HTTP_UNPROCESSABLE_ENTITY);
            } elseif (Carbon::now() > Carbon::parse($sendOtp->otp_expire_at)) {
                return $this->sendError(__('passwords.otp_expire'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $email = $sendOtp->email;

            if (!$email) {
                return $this->sendError(__('app.exist', ['attribute' => __('app.email')]));
            }

            $request->request->add(['email' => $email]);

            $user = new Account($request->all());
            $user->createAccount();

            $buyer = new Buyer();
            $buyer['account_name'] = $request->name;
            $buyer['account_id'] = $user->id;
            $buyer->save();

            $seller = new Seller();
            $seller['account_name'] = $request->name;
            $seller['account_id'] = $user->id;
            $seller->save();

            $sendOtp->delete();

            $data = array(
                'APP_URL' => config('app.url'),
                'name' => $request->name
            );

            $title = __('app.title_mail_account_registered');
            $this->sendEmail('email.email-registered-account', $email, $data, $title);
            DB::commit();
            $token = auth(UserConst::USER_GUARD)->setTTL(43200)->attempt(['email' => $email, 'password' =>  $request->password]);
            //Password updated, return with success response
            $results = $this->convertDataToken($token, $user->id, $this->account->getTypeAccount($user->id), UserConst::USER_GUARD);
            return $this->sendSuccessResponse($results);
            // return $this->sendSuccessResponse($user['email']);
        } catch (Exception $e) {
            $this->log("registerMemberStep1", null, $request->all(), $e->getMessage());
            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function registerMemberStep2($id, Request $request)
    {
        try {
            $rules = $this->registerStep2Rules();
            $validator = Validator::make($request->all(), $rules);
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            $user_info = $this->account->find($id);
            if (!$user_info) {
                return $this->sendError(__('app.not_exist', ['attribute'  => __('app.user')]));
            }
            $this->account->where('id', $id)->update($request->all());
            return $this->sendSuccess(__('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.user')]));
        } catch (Exception $e) {
            $this->log("checkActiveLinkExpired", $id, $request, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function checkActiveLinkExpired($token, Request $request)
    {
        try {
            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($token);
            $email = $decryptToken[0];
            $registered_member = $this->account->getInfoAccountByEmail($email);
            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();
            if ($time_now > $expire_at) {
                return $this->sendError(__('app.active_link_expired'));
            } else if ($registered_member) {
                return $this->sendError('既に会員登録されています');
            }
            return $this->sendSuccess(__('app.active_link_valid'));
        } catch (Exception $e) {
            $this->log("checkActiveLinkExpired", null, $token, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
