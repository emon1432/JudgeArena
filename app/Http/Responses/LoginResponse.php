<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            $home = "/user/{$user->username}";
        } elseif ($user->role === 'admin') {
            $home = '/admin/dashboard';
        }

        return redirect()->intended($home);
    }
}
