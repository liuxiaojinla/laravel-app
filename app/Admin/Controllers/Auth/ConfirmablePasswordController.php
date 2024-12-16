<?php

namespace App\Admin\Controllers\Auth;

use App\Http\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Xin\Hint\Facades\Hint;

class ConfirmablePasswordController extends Controller
{

    /**
     * Confirm the user's password.
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::guard('admin')->validate([
            'account' => $request->user()->account,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return Hint::success("确认成功！");
    }
}
