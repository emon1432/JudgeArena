<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $username)
    {
        return view('web.pages.user.show', compact('username'));
    }

    public function platformProfile(string $username, string $platform)
    {
        return view('web.pages.user.platform', compact('username', 'platform'));
    }

    public function edit(string $username)
    {
        return view('web.pages.user.edit', compact('username'));
    }

    public function update(Request $request, string $username)
    {
        return redirect()->route('user.show', $username)->with('success', 'Profile updated successfully.');
    }
}
