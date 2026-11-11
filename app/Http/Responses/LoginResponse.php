<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Selamat datang kembali.']);

        return redirect()->intended(Fortify::redirects('login'));
    }
}
