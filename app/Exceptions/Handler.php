<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (Exception $e, $request) {
            if ($request->is('api/*')) {
                $e = $this->prepareException($e);

                if ($e instanceof HttpResponseException) {
                    $e = $e->getResponse();
                }

                if ($e instanceof AuthenticationException) {
                    $e = $this->unauthenticated($request, $e);
                }

                if ($e instanceof ValidationException) {
                    $e = $this->convertValidationExceptionToResponse($e, $request);
                }

                if (method_exists($e, 'getStatusCode')) {
                    $statusCode = $e->getStatusCode();
                } else {
                    $statusCode = 500;
                }
                $response = [];

                switch ($statusCode) {
                    case 401:
                        $response['success'] = false;
                        $response['message'] = 'Unauthorized';
                        break;
                    case 403:
                        $response['success'] = false;
                        $response['message'] = 'Forbidden';
                        break;
                    case 404:
                        $response['success'] = false;
                        $response['message'] = 'Not Found';
                        break;
                    case 405:
                        $response['success'] = false;
                        $response['message'] = 'Method Not Allowed';
                        break;
                    case 422:
                        $response['success'] = false;
                        $response['message'] = $e->original['message'];
                        $response['errors'] = $e->original['errors'];
                        break;
                    case 500:
                        $response['success'] = false;
                        $response['message'] = $e->getMessage();
                        break;
                    default:
                        $response['message'] = $e->getMessage();
                        break;
                }

                if (config('app.debug')) {
                    // $response['trace'] = $e->getTrace();
                    $response['code'] = $e->getCode();
                }

                $response['status'] = $statusCode;
                return response()->json($response, $statusCode);
            }
        });
    }
}
