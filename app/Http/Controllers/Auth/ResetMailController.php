<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\EmailReset;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Response;
use App\Constants\UserConst;
use App\Models\Account;
use Illuminate\Support\Facades\Crypt;

class ResetMailController extends BaseController
{
    protected $emailReset;
    protected $account;

    public function __construct(EmailReset $emailReset, Account $account)
    {
        $this->emailReset = $emailReset;
        $this->account = $account;
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
    public function rulesMailReset()
    {
        return [
            'token' => 'required|string|max:60',
        ];
    }

    /**
     * Create token email reset.
     *  @OA\Post(
     *     path="/api/mail/input",
     *     summary="Reset Email Account",
     *     tags={"Auth"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="email",
     *                             type="string",
     *                             example=""
     *                         ),
     *        ),
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Create Token Reset Email Account Successful",
     *     ),
     *     @OA\Response(
     *        response="403",
     *        description="Forbidden",
     *     ),
     *  )
     * @param  Request $request
     * @return JsonResponse
     */
    public function sendMailUser(Request $request)
    {
        $credentials = $request->only('email');

        //valid credential
        $validator = Validator::make($credentials, $this->rulesMail());

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first());
        }

        // check email exist
        $account_email = $this->account->getInfoAccountByEmail($request->email);
        if (isset($account_email)) {
            return $this->sendError(__('app.exist', ['attribute' => __('app.email')]));
        }

        // get user logged in
        $account = auth(UserConst::USER_GUARD)->user();
        $user = $this->account->getInfoAccountById($account->id);
        // create token
        $token = Str::random(60);
        // data create or update
        $data = [
            'user_id' => $account->id,
            'new_email' => $request->email,
            'auth_key' => $token,
            'expiration' => Carbon::now()->addDays(1),
            'created_at' => Carbon::now(),
        ];
        $mailReset = $this->emailReset->updateOrCreate($data);

        $url = config('app.url') . UserConst::RESET_MAIL_PATH . $token;

        if ($mailReset) {
            try {
                $title = __('app.title_mail_reset_mail');
                // data send mail reset email
                $data = array(
                    'url' => $url,
                    'name' => $user->buyers->account_name,
                );
                $this->sendEmailHaveHtml('email.email-reset-mail', $request->email, $data, $title);
            } catch (Exception $exception) {
                error_log($exception);
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        //Sent mail, return with success response
        return $this->sendSuccessResponse();
    }

    /**
     *  Email reset.
     *  @OA\Post(
     *     path="/api/mail/reset",
     *     summary="Reset Email Account",
     *     tags={"Auth"},
     *     security={ {"bearer": {}} },
     *     @OA\RequestBody(
     *        required = true,
     *        @OA\JsonContent(
     *             type="object",
     *                         @OA\Property(
     *                             property="token",
     *                             type="string",
     *                             example=""
     *                         ),
     *        ),
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Reset Email Account Successful",
     *     ),
     *     @OA\Response(
     *        response="403",
     *        description="Forbidden",
     *     ),
     *  )
     * @param  Request $request
     * @return JsonResponse
     */
    public function resetMailUser(Request $request)
    {
        try {
            $credentials = $request->only('token');

            //valid credential
            $validator = Validator::make($credentials, $this->rulesMailReset());

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first());
            }

            // Find token in mailReset table
            $mailReset = $this->emailReset->findByAuthKey($request->token);
            if (!$mailReset)
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.token')]));
            $toke_expire = new Carbon($mailReset->expiration);
            if ((now()->getTimestamp() - $toke_expire->getTimestamp()) > 0) {
                $mailReset->delete();
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.token')]));
            }

            // check email exist
            $account_email = $this->account->getInfoAccountByEmail($mailReset->new_email);
            if (isset($account_email)) {
                return $this->sendError(__('app.exist', ['attribute' => __('app.email')]));
            }

            // Reset mail by user id
            $info = $this->account->find($mailReset->user_id);
            if (!isset($info)) {
                return $this->sendError(__('app.not_exist', ['attribute' => __('app.user')]));
            }
            $updateMailUser = $info->updateEmailByAccountId($mailReset->user_id, $mailReset->new_email);
            if ($updateMailUser < 1)
                return $this->sendError(__('app.system_error'));

            // Delete token reset mail
            $mailReset->delete();

            // Mail updated, return with success response
            return $this->sendSuccessResponse();
        } catch (Exception $e) {
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
