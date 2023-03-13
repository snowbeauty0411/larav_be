<?php


namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Mail;

use SendGrid;
use SendGrid\Mail\From;
use SendGrid\Mail\HtmlContent;
use SendGrid\Mail\Mail;
use SendGrid\Mail\PlainTextContent;
use SendGrid\Mail\Subject;
use SendGrid\Mail\To;

class BaseController extends Controller
{
    /**
     * return success response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendSuccessResponse($data = null)
    {
    	$response = [
            'success' => true,
        ];

        if (isset($data)) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error = null, $code = null)
    {
    	$response = [
            'success' => false,
            'message' => $error,
            'code' => $code
        ];

        if ($code) {
            return response()->json($response, $code);
        } else {
            return response()->json($response);
        }
    }

    public function sendSuccess($message=null)
    {
        $response = [
            'success' => true,
            'message' =>$message
        ];
        return response()->json($response);
    }

    /**
     * Send Email Function
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmail($page, $toEmail, $data, $title)
    {
        //CCをやめました　2021.07.01 by cheat
        $from_email = config('mail.from')['address'];
        $name_email = config('mail.from')['name'];
        // $cc_email = config('mail.from')['cc'];
        // $cc_email = explode(',', $cc_email);
        if (!$from_email) {
            return back()->with('fail', __('app.auth_register_err'))->withInput();
        }
        $data['user_name'] = $data['id'] ?? null;
        $data['APP_URL'] = config('app.url');
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from_email, $name_email);
        $email->setSubject($title);
        if(gettype($toEmail) == 'array') {
            $email->addTos($toEmail);
        } else {
            $email->addTo($toEmail);
        }
        $email->addContent(
            "text/plain",
            strval(
                view(
                    $page,
                    $data
                )
            )
        );

        $apikey = config('mail.from')['key'];

        $sendGrid = new \SendGrid($apikey);

        try {
            $sendGrid->send($email);
            return [
                'status' => true,
                'message' => __('app.action_success', ['attribute' => __('app.registration').__('app.email').__('app.send'),'action' => __('app.send')])
            ];
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return [
                'status' => true,
                'message' => __('app.action_failed', ['attribute' => __('app.registration').__('app.email').__('app.send'),'action' => __('app.send')])
            ];
        }

    }

    /**
     * Send Email Function
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmailHaveHtml($page, $toEmail, $data, $title)
    {
        //CCをやめました　2021.07.01 by cheat
        $from_email = config('mail.from')['address'];
        $name_email = config('mail.from')['name'];
        // $cc_email = config('mail.from')['cc'];
        // $cc_email = explode(',', $cc_email);
        if (!$from_email) {
            return back()->with('fail', __('app.auth_register_err'))->withInput();
        }
        $data['user_name'] = $data['id'] ?? null;
        $data['APP_URL'] = config('app.url');
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($from_email, $name_email);
        $email->setSubject($title);
        if(gettype($toEmail) == 'array') {
            $email->addTos($toEmail);
        } else {
            $email->addTo($toEmail);
        }
        $email->addContent(
            "text/html",
            strval(
                view(
                    $page,
                    $data
                )
            )
        );

        $apikey = config('mail.from')['key'];

        $sendGrid = new \SendGrid($apikey);

        try {
            $sendGrid->send($email);
            return [
                'status' => true,
                'message' => __('app.action_success', ['attribute' => __('app.registration').__('app.email').__('app.send'),'action' => __('app.send')])
            ];
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return [
                'status' => true,
                'message' => __('app.action_failed', ['attribute' => __('app.registration').__('app.email').__('app.send'),'action' => __('app.send')])
            ];
        }

    }


    /**
     * log to file.
     *
     * @return \Illuminate\Http\Response
     */
    public function log($funct = null, $user_id = null, $data = null, $message)
    {
        $log = 'ERROR: ';
        $log = $log.$funct;
        if (!is_null($user_id)) $log = $log.' user_id:'.$user_id;
        if (!is_null($data)) $log = $log.' data: {'.PHP_EOL.json_encode($data, JSON_UNESCAPED_UNICODE).'}';
        $log = $log.' message:'.$message;
    	Log::channel('errorlogcustom')->info($log);
    }

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

    protected function convertDataToken($token, $account_id, $type, $guard)
    {
        return [
            'access_token' => $token,
            'account_id' => $account_id,
            'type' => $type,
            'token_type' => 'bearer',
            'expires_in' => auth($guard)->factory()->getTTL()
        ];
    }
}
