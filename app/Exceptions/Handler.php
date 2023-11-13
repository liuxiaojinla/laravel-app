<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Throwable;
use Xin\Hint\Facades\Hint;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
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
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareResponse($request, Throwable $e)
    {
        if ($this->isHttpException($e) && $e->getStatusCode() == 419) {
            return Redirect::refresh();
        }

        return parent::prepareResponse($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $url = $exception->redirectTo() ?? '/login';
        return $request->expectsJson()
            ? Hint::error($exception->getMessage(), $url, -1)
            : redirect()->guest($url);
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
        $modulesConfig = config('module.modules');
        $patterns = Arr::map($modulesConfig, function ($config, $key) {
            if (!($config['exceptionShouldReturnJson'] ?? true)) {
                return null;
            }
            $prefix = $config['prefix'] ?? $key;
            return $prefix . "/*";
        });
        $patterns = array_filter($patterns);
        return $request->expectsJson() || $request->is(...$patterns);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        $code = $this->isHttpException($e) ? $e->getStatusCode() : 0;
        return config('app.debug') ? [
            'code' => $code,
            'msg' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'code' => $code,
            'msg' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
}
