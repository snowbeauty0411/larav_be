<?php

namespace App\Http\Controllers\Auth;

use App\Constants\SendOtpConst;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Response;
use App\Constants\UserConst;
use App\Models\Account;
use App\Models\SendOtp;
use Exception;
use Illuminate\Support\Facades\Crypt;

class ResetPasswordController extends BaseController
{
    protected $account;
    protected $sendOtp;

    public function __construct(
        Account $account,
        sendOtp $sendOtp
    ) {
        $this->account = $account;
        $this->sendOtp = $sendOtp;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesMail()
    {
        return [
            'email' => 'required|string|email|max:256',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesPassword()
    {
        return [
            'password' => 'required|string|confirmed|max:30',
            'token' => 'required|string|max:60',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function ruleToken()
    {
        return [
            'token' => 'required|string|max:60',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesPasswordOTP()
    {
        return [
            'password' => 'required|string|confirmed|max:30',
            'otp' => 'required|integer|min:100000|max:999999',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function ruleUserLogin()
    {
        return [
            'password' => 'required|string|confirmed|max:30',
        ];
    }

    /**
     * Create token password reset.
     *
     * @param  Request $request
     * @return JsonResponse
     *     @OA\Post(
     *     path="/api/forgot/input",
     *     summary="Forgot password",
     *     tags={"Reset user's password"},
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
    public function sendMailUser(Request $request)
    {
        $credentials = $request->only('email');

        //valid credential
        $validator = Validator::make($credentials, $this->rulesMail());

        //Send failed response if request is not valid
        $errors = $validator->errors();
        if ($errors->first()) {
            return $this->sendError($errors->first());
        }

        // get Account
        $info = $this->account->getInfoAccountByEmail($request->email);
        if (!$info) {
            return $this->sendError(__('app.not_exist', ['attribute' => __('app.email')]));
        }

        //get User by email and create token
        $token = Str::random(60);
        $passwordReset = PasswordReset::updateOrCreate([
            'account_id' => $info->id,
            'token' => $token,
            'expiration' => Carbon::now()->addHours(12),
        ]);

        $url = config('app.url') . UserConst::RESET_PASSWORD_PATH . $token;

        if ($passwordReset) {
            try {
                $title = __('app.title_mail_reset_password');
                // data send password reset email
                $data = array(
                    'url' => $url,
                    'name' => $info->buyers->account_name
                );
                error_log($token);
                $this->sendEmailHaveHtml('email.email-reset-password', $info->email, $data, $title);
            } catch (Exception $e) {
                $this->log("sendMailResetPass", null, $request->all(), $e->getMessage());
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        //Sent mail, return with success response
        return $this->sendSuccess(__('app.send_mail_success'));
    }

    /**
     * Create token password reset.
     *
     * @param  Request $request
     * @return JsonResponse
     *     @OA\Post(
     *     path="/api/mobile/forgot/input",
     *     summary="Forgot password use OTP",
     *     tags={"Reset user's password"},
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
    public function sendMailOTPUser(Request $request)
    {
        $credentials = $request->only('email');

        //valid credential
        $validator = Validator::make($credentials, $this->rulesMail());

        //Send failed response if request is not valid
        $errors = $validator->errors();
        if ($errors->first()) {
            return $this->sendError($errors->first());
        }

        // get Account
        $info = $this->account->getInfoAccountByEmail($request->email);
        
        if (!$info) {
            return $this->sendError(__('app.not_exist', ['attribute' => __('app.email')]));
        }

        //create OTP
        $otp = $this->sendOtp->generateOtp();

        //save OTP
        $sendOtp = $this->sendOtp->updateOrCreate([
            'email' => $request->email,
            'account_id' => $info->id,
            'otp_type' => SendOtpConst::RESET_PASSWORD,
        ], [
            'otp' => $otp,
            'otp_expire_at' => Carbon::now()->addMinutes(UserConst::TIME_EXPIRE_OTP),
        ]);

        //URL reset password
        $url = config('app.url') . UserConst::RESET_PASSWORD_PATH;

        if ($sendOtp) {
            try {
                $title = __('app.title_mail_reset_password');
                // data send password reset email
                $data = array(
                    'url' => $url,
                    'name' => $info->buyers->account_name,
                    'otp' => $otp
                );
                $this->sendEmail('email.email-reset-password-use-otp', $info->email, $data, $title);
            } catch (Exception $e) {
                $this->log("sendMailOTPUser", null, $request->all(), $e->getMessage());
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        //Sent mail, return with success response
        return $this->sendSuccess(__('app.send_mail_success'));
    }


    /**
     * Password reset.
     *
     * @param  Request $request
     * @return JsonResponse
     *     @OA\Post(
     *     path="/api/password/reset",
     *     summary="Password reset",
     *     tags={"Reset user's password"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"password", "password_confirmation", "token"},
     *                        @OA\Property(
     *                             property="password",
     *                             example="password",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="password_confirmation",
     *                             example="password",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="token",
     *                             example="vxas4xDWy4WpI9UrSbDIUZgKintAXYn5lQSS3W4qOkZRus31MlMxXKr18Xyl",
     *                             type="string",
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="User login successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function resetPassUser(Request $request)
    {
        try {
            // check login
            $user = auth(UserConst::USER_GUARD)->user();
            if ($user) {
                $credentials = $request->only('password', 'password_confirmation');

                //valid credential
                $validator = Validator::make($credentials, $this->ruleUserLogin());

                //Send failed response if request is not valid
                $errors = $validator->errors();
                if ($errors->first()) {
                    return $this->sendError($errors->first());
                }

                // Reset password by user id
                $info = $this->account->with('buyers')->find($user->id);

                $updatePasswordUser = $info->updatePasswordByAccountId($info->id, $request->password);
            } else {
                $credentials = $request->only('password', 'password_confirmation', 'token');

                //valid credential
                $validator = Validator::make($credentials, $this->rulesPassword());

                //Send failed response if request is not valid
                $errors = $validator->errors();
                if ($errors->first()) {
                    return $this->sendError($errors->first());
                }

                // Find token in PasswordReset table
                $passwordReset = PasswordReset::where('token', $request->token)->first();
                $expiration = $passwordReset->expiration;
                $currentTime = Carbon::now()->toDateTimeString();
                if ($currentTime > $expiration) {
                    return $this->sendError(__('passwords.expiration'));
                }
                if (!$passwordReset)
                    return $this->sendError(__('passwords.token'), Response::HTTP_UNPROCESSABLE_ENTITY);
                if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
                    $passwordReset->delete();
                    return $this->sendError(__('passwords.token'), Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Reset password by user id
                $info = $this->account->with('buyers')->find($passwordReset->account_id);

                $updatePasswordUser = $info->updatePasswordByAccountId($info->id, $request->password);

                // Delete token reset password
                $passwordReset->delete();
            }

            $title = __('app.title_mail_reset_password_complete');
            // data send password reset email
            $data = array(
                'name' => $info->buyers->account_name
            );
            $this->sendEmail('email.email-reset-password-complete', $info->email, $data, $title);
            $token = auth(UserConst::USER_GUARD)->setTTL(43200)->attempt(['email' => $info['email'], 'password' =>  $credentials['password']]);

            //Password updated, return with success response
            $results = $this->convertDataToken($token, $info['id'], $this->account->getTypeAccount($info['id']), UserConst::USER_GUARD);
            return $this->sendSuccessResponse($results);
            // return $this->sendSuccessResponse($info);
        } catch (Exception $e) {
            $this->log("resetPassAccount", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Password reset.
     *
     * @param  Request $request
     * @return JsonResponse
     *     @OA\Post(
     *     path="/api/mobile/password/reset",
     *     summary="Password reset by otp",
     *     tags={"Reset user's password"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"password", "password_confirmation", "otp"},
     *                        @OA\Property(
     *                             property="password",
     *                             example="password",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="password_confirmation",
     *                             example="password",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="otp",
     *                             example=123456,
     *                             type="integer",
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="User login successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function resetPasswordUseOTP(Request $request)
    {
        try {
        
            $credentials = $request->only('password', 'password_confirmation', 'otp');

            //valid credential
            $validator = Validator::make($credentials, $this->rulesPasswordOTP());

            //Send failed response if request is not valid
            $errors = $validator->errors();
            if ($errors->first()) {
                return $this->sendError($errors->first());
            }

            //find otp in reset_password_otp table
            $sendOtp = $this->sendOtp->getByOTP($request->otp, SendOtpConst::RESET_PASSWORD);
            
            //check OTP valid or expire
            if (!$sendOtp) {
                return $this->sendError(__('passwords.otp'), Response::HTTP_UNPROCESSABLE_ENTITY);
            } elseif (Carbon::now() > Carbon::parse($sendOtp->otp_expire_at)) {
                return $this->sendError(__('passwords.otp_expire'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            //get info user need reset password
            $info = $this->account->getInfoAccountById($sendOtp->account_id);

            $this->account->updatePasswordByAccountId($info->id, $request->password);
            
            //delete OTP after reset password to success
            $sendOtp->delete();

            $title = __('app.title_mail_reset_password_complete');
            // data send password reset email
            $data = array(
                'name' => $info->buyers->account_name
            );
            
            //send mail notify after reset password success
            $this->sendEmail('email.email-reset-password-complete', $info->email, $data, $title);

            //create authentication token after reset password success
            $token = auth(UserConst::USER_GUARD)->setTTL(43200)->attempt(['email' => $info['email'], 'password' =>  $credentials['password']]);

            //Password updated, return with success response
            $results = $this->convertDataToken($token, $info['id'], $this->account->getTypeAccount($info['id']), UserConst::USER_GUARD);
            return $this->sendSuccessResponse($results);
            
        } catch (Exception $e) {
            $this->log("resetPasswordUseOTP", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Password reset.
     *
     * @param  Request $request
     * @return JsonResponse
     *     @OA\Post(
     *     path="/api/password/check",
     *     summary="check token Password",
     *     tags={"Reset user's password"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="token",
     *                             example="vxas4xDWy4WpI9UrSbDIUZgKintAXYn5lQSS3W4qOkZRus31MlMxXKr18Xyl",
     *                             type="string",
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="User login successful",
     *     ),
     *     @OA\Response(
     *        response="400",
     *        description="Bad Request",
     *     ),
     *  *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function checkTokenPassword(Request $request)
    {
        try {
                $validator = Validator::make($request->all(), $this->ruleToken());
                $errors = $validator->errors();
                if ($errors->first()) {
                    return $this->sendError($errors->first());
                }

                $passwordReset = PasswordReset::where('token', $request->token)->first();
                if ($passwordReset) {
                    $expiration = $passwordReset->expiration;
                    $currentTime = Carbon::now()->toDateTimeString();
                    if ($currentTime > $expiration) {
                        return $this->sendError(__('passwords.expiration'));
                    } else {
                        return $this->sendSuccessResponse();
                    }
                } else {
                    return $this->sendError(__('app.exist', ['attribute' => __('app.token')]));
                }
        } catch (Exception $e) {
            $this->log("checkTokenPassword", null, $request->all(), $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
 