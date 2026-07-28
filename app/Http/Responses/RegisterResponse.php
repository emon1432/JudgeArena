<?php

namespace App\Http\Responses;

use App\Models\User;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = User::findOrFail(Auth::id());
        $home = $user->role === 'admin' ? '/dashboard' : "/user/{$user->username}";

        return redirect()->intended($home);
    }
}
