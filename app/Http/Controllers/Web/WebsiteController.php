<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        return view('web.pages.home.index');
    }

    public function platforms()
    {
        return view('web.pages.platforms.index');
    }

    public function platformDetail(string $slug)
    {
        return view('web.pages.platforms.show');
    }

    public function contests()
    {
        return view('web.pages.contests.index');
    }

    public function problems()
    {
        return view('web.pages.problems.index');
    }

    public function rankings()
    {
        return view('web.pages.rankings.index');
    }

    public function community()
    {
        return view('web.pages.community.index');
    }
}
