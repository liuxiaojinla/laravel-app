<?php

namespace App\Exceptions;

use App\Supports\WebServer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
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

        $this->renderable(function (HttpResponseException $e, Request $request) {
            if ($this->shouldReturnJson($request, $e)) {
                $response = $e->getResponse();
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    return Hint::success($response->getContent());
                } else {
                    return Hint::error($response->getContent(), $response->getStatusCode());
                }
            }
        });
    }


    /**
     * @inerhitDoc
     */
    protected function unauthenticatedJson(AuthenticationException $exception)
    {
        $url = $exception->redirectTo();
        return Hint::error($exception->getMessage(), Error::UNAUTHENTICATED, $url);
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
        return WebServer::shouldReturnJson($request);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        $isDebug = config('app.debug');

        $code = $this->isHttpException($e) ? $e->getStatusCode() : 0;
        $message = $e->getMessage();
        $previous = $e->getPrevious();

        if ($previous instanceof ModelNotFoundException) {
            $modelClass = $e->getPrevious()->getModel();
            $prefix = $isDebug ? "[$modelClass] " : (const_exist($modelClass, 'TITLE') ? $modelClass::TITLE . ' ' : '');
            $message = $prefix . "数据不存在！";
        }

        return $isDebug ? [
            'code'      => $code,
            'msg'       => $message,
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => collect($e->getTrace())->map(fn($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'code' => $code,
            'msg'  => $this->isHttpException($e) ? $message : 'Server Error',
        ];
    }
}
