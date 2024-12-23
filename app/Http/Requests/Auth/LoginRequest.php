<?php

namespace App\Http\Requests\Auth;

use App\Exceptions\Error;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Xin\Support\Arr;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'sometimes|required|string',
            'account' => 'required_without:username|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $credentials = $this->only(['username', 'account', 'password']);
        $remember = $this->boolean('remember');

        $credentials = Arr::transformKeys($credentials, ['account' => 'username']);
        $account = $credentials['username'];

        $this->ensureIsNotRateLimited($account);

        $user = null;
        if (!Auth::attemptWhen($credentials, function (User $tempUser) use (&$user) {
            $user = $tempUser;
            return $user->status === 1;
        }, $remember)) {
            RateLimiter::hit($this->throttleKey($account));

            if ($user->status !== 1) {
                throw Error::validationException("用户" . $user->status_text);
            }

            throw ValidationException::withMessages([
                'default' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($account));

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited($account): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($account), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey($account));

        throw ValidationException::withMessages([
            'default' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey($account): string
    {
        return Str::transliterate("user|" . Str::lower($account) . '|' . $this->ip());
    }
}
