<?php

namespace App\Exceptions;

use App\Supports\WebServer;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException as EasyWeChatInvalidConfigException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Foundation\Exceptions\Handler as ExceptionHandler;
use Yansongda\Artful\Exception\InvalidConfigException as PayInvalidConfigException;

class Handler extends ExceptionHandler
{

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });

        // 转换相关的异常
        $this->map(function (Throwable $e) {
            if ($e instanceof PayInvalidConfigException) {
                return new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            } elseif ($e instanceof EasyWeChatInvalidConfigException) {
                return new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }

            return $e;
        });

        // 拦截特殊的异常进行响应
        $this->renderable(function (HttpResponseException $e, Request $request) {
            if ($this->shouldReturnJson($request, $e)) {
                $response = $e->getResponse();
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    return $this->responseAddCorsHeaders(
                        Hint::success($response->getContent())
                    );
                } else {
                    return $this->responseAddCorsHeaders(
                        Hint::error($response->getContent(), $response->getStatusCode())
                    );
                }
            }

            return null;
        });
    }

    /**
     * Determine if the exception handler response should be JSON.
     *
     * @param Request $request
     * @param Throwable $e
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return WebServer::shouldReturnJson($request);
    }

    /**
     * @inerhitDoc
     */
    protected function unauthenticatedJson(AuthenticationException $exception)
    {
        $url = $exception->redirectTo();
        return $this->responseAddCorsHeaders(
            Hint::error($exception->getMessage(), Error::UNAUTHENTICATED, $url)
        );
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
        return $this->responseAddCorsHeaders(
            Hint::error(
                $msg, $exception->status, null,
                [
                    'errors' => $exception->errors(),
                ]
            )
        );
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $result = $this->convertExceptionToArray($e);
        return $this->responseAddCorsHeaders(
            Hint::error(
                $result['msg'], $result['code'], $request->url(), $result
            )
        );
    }

    /**
     * Convert the given exception to an array.
     *
     * @param Throwable $e
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
            'code' => $code,
            'msg' => $message,
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'code' => $code,
            'msg' => $this->isSafetyException($e) ? $message : 'Server Error',
        ];
    }

    /**
     * 是否是安全的异常
     * @param Throwable $e
     * @return bool
     */
    protected function isSafetyException($e)
    {
        return $this->isHttpException($e) || $e instanceof \LogicException;
    }
}
