<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Foundation\Exceptions\Handler as ExceptionHandler;

//use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
    }


    /**
     * @inerhitDoc
     */
    protected function unauthenticatedJson(AuthenticationException $exception)
    {
        $url = $exception->redirectTo() ?? route('login');
        return Hint::error($exception->getMessage(), $url, -1);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param Request $request
     * @param ValidationException $exception
     * @return Response
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $msg = current($exception->errors())[0];
        return Hint::error(
            $msg,
            $exception->status,
            null,
            [
                'errors' => $exception->errors(),
            ]
        );
    }

    /**
     * Determine if the exception handler response should be JSON.
     *
     * @param Request $request
     * @param \Throwable $e
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->expectsJson() || $request->is([
                'api/*',
                'admin/*',
                'notify/*',
            ]);
    }
}
