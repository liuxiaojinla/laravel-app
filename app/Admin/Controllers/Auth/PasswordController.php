<?php

namespace App\Admin\Controllers\Auth;

use App\Admin\Models\Admin;
use App\Http\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Xin\Hint\Facades\Hint;

class PasswordController extends Controller
{

    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        Admin::unguarded(function () use ($request, $validated) {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        });

        return Hint::success(__('passwords.updated'));
    }
}
