<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Constants\UserConst;
use App\Models\Account;
use App\Models\User;
use App\Models\LoginHistory;
use Carbon\Carbon;

class LoginController extends BaseController
{
    protected $user;
    protected $account;
    protected $loginHistory;

    public function __construct( User $user,Account $account, LoginHistory $loginHistory)
    {
        $this->user = $user;
        $this->account = $account;
        $this->loginHistory = $loginHistory;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|string|email|max:256',
            'password' => 'required|string|max:30',
        ];
    }

    /**
     * Get the validation rules minashi login that apply to the request.
     *
     * @return array
     */
    public function rulesMinashiLogin()
    {
        return [
            'user_id' => 'required|integer'
        ];
    }

    /**
     *     @OA\Post(
     *     path="/api/login",
     *     summary="Members login",
     *     tags={"Auth"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"email","password"},
     *                        @OA\Property(
     *                             property="email",
     *                             example="seller@gmail.com",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             example="12345678",
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
    public function loginUser(Request $request)
    {
        // get current ip
        $ip = $request->getClientIp();
        $block_flg = $this->blockLogin($ip);
        if ($block_flg) {
            return $this->sendError(__('app.block_login'));
        }

        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, $this->rules());
        $errors = $validator->errors();
        // //Send failed response if request is not valid
        if ($errors->first()) return $this->sendError($errors->first());

        $account = $this->account->getInfoAccountByEmail($request->email);
        // $account = $this->user->getInfoUserByEmail($request->email);
        if (!isset($account)) {
            return $this->sendError(__('app.email_not_exist'));
        }
        if ($account->date_withdrawal)
            return $this->sendError(__('app.withdrawal'));
        if ($account->is_blocked === UserConst::UNSUBSCRIBED)
            return $this->sendError(__('app.unsubscribe'));
        if ($account->is_blocked === UserConst::BLOCKED)
            return $this->sendError(__('app.blocked'));
        //Request is validated
        //Create token
        try {
            if (! $token = auth(UserConst::USER_GUARD)->setTTL(43200)->attempt($credentials)) {
                // find by ip
                $login_histories = $this->loginHistory->findByIp($ip);
                if(isset($login_histories)) {
                    if($login_histories->count_failed>=UserConst::LOGIN_FAILED_LIMIT-1) {
                        $login_histories->count_failed += 1;
                        $login_histories->block_at = Carbon::now()->addMinutes(UserConst::LOGIN_BLOCK_TIME);
                        $login_histories->save();
                    } else {
                        $login_histories->count_failed += 1;
                        $login_histories->save();
                    }
                } else {
                    $data = [
                        'ip_address' => $ip,
                        'count_failed' => 1
                    ];
                    // create history
                    $this->loginHistory->create($data);
                }
                return $this->sendError(__('app.login_failed'));
            } else {
                // find by ip
                $login_histories = $this->loginHistory->findByIp($ip);
                if(isset($login_histories)) {
                    $login_histories->count_failed = 0;
                    $login_histories->block_at = NULL;
                    $login_histories->save();
                }
            }
        } catch (JWTException $e) {
    	// return $credentials;
            return $this->sendError(__('app.create_token_failed'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //Token created, return with success response and jwt token\
        $results = $this->convertDataToken($token, $account['id'], $this->account->getTypeAccount($account['id']), UserConst::USER_GUARD);
        return $this->sendSuccessResponse($results);
    }

    /**
     *     @OA\Post(
     *     path="/api/admin/login",
     *     summary="Admin login",
     *     tags={"Auth"},
     *      @OA\RequestBody(
     *        @OA\JsonContent(
     *             type="object",
     *                        required={"email","password"},
     *                        @OA\Property(
     *                             property="email",
     *                             example="admin@gmail.com",
     *                             type="string",
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             example="12345678",
     *                             type="string",
     *                         ),
     *         )
     *     ),
     *     @OA\Response(
     *        response="200",
     *        description="Admin login successful",
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
    public function loginAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, $this->rules());
        $errors = $validator->errors();
        // //Send failed response if request is not valid
        if ($errors->first()) return $this->sendError($errors->first());

        //Request is validated
        //Crean token
        try {
            if (! $token = auth(UserConst::ADMIN_GUARD)->attempt($credentials)) {
                return $this->sendError(__('app.login_failed'));
            }
        } catch (JWTException $e) {
    	return $credentials;
            return $this->sendError(__('app.create_token_failed'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

 		//Token created, return with success response and jwt token
        $results = $this->convertDataToken($token, null, null , UserConst::ADMIN_GUARD);
        return $this->sendSuccessResponse($results);
    }

    /**
     *     @OA\Get(
     *     path="/api/logout",
     *     summary="Members logout",
     *     tags={"Auth"},
     *     security={ {"bearer": {}} },
     *     @OA\Response(
     *        response="200",
     *        description="User login successful",
     *     ),
     *     @OA\Response(
     *        response="401",
     *        description="許可がありません。",
     *     ),
     *     @OA\Response(
     *        response="500",
     *        description="Internal Server Error",
     *     ),
     * )
     *
     */
    public function logout(Request $request)
    {
        try {
            auth(UserConst::USER_GUARD)->logout();
            return $this->sendSuccessResponse();
        } catch (JWTException $e) {
            $this->log("logout", null, null, $e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @param  string $guard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $account_id, $type, $guard)
    {
        return response()->json([
            'access_token' => $token,
            'account_id' => $account_id,
            'type' => $type,
            'token_type' => 'bearer',
            'expires_in' => auth($guard)->factory()->getTTL()
        ]);
    }

    /**
     * Block login.
     *
     * @param  string $ip
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function blockLogin($ip)
    {
        // find by ip
        $login_histories = $this->loginHistory->findByIp($ip);
        if(isset($login_histories)&&$login_histories->count_failed>=UserConst::LOGIN_FAILED_LIMIT){
           $block_at = new Carbon($login_histories->block_at);
           // check if it's still in block time
           if ((now()->getTimestamp()-$block_at->getTimestamp())>0) {
                $login_histories->count_failed = 0;
                $login_histories->block_at = NULL;
                $login_histories->save();
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}
