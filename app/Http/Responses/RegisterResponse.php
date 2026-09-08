<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = User::findOrFail(Auth::id());
        $home = $user->role === 'admin' ? '/admin/dashboard' : "/user/{$user->username}";

        return redirect()->intended($home);
    }
}
