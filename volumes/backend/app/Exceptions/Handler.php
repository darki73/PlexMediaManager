<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception) : void {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Exception  $exception
     * @return Response
     */
    public function render($request, Exception $exception) {
        if (false !== stripos($exception->getMessage(), 'Route [login] not defined.')) {
            return response()->json(
                [
                    'success'       =>  false,
                    'message'       =>  'No (or invalid) authentication token provided, hence, the request has been blocked by the server',
                    'data'          =>  [
                        'status'    =>  Response::HTTP_UNAUTHORIZED,
                        'message'   =>  'Unauthenticated',
                    ]
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        if (false !== stripos($exception->getMessage(), 'User does not have the right roles.')) {
            return response()->json(
                [
                    'success'       =>  false,
                    'message'       =>  'You are not allowed to access this resource as you dont have sufficient permissions',
                    'data'          =>  [
                        'status'    =>  Response::HTTP_FORBIDDEN,
                        'message'   =>  'Forbidden',
                    ]
                ], Response::HTTP_FORBIDDEN
            );
        }

        return parent::render($request, $exception);
    }

}
