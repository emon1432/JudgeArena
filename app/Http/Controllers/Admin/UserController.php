<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use App\View\Components\UserInfo;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }
        return view('admin.pages.users.index');
    }

    public function show(User $user)
    {
        dd($user);
    }

    protected function data(Request $request): array
    {
        return ServerSideDatatable::make(
            $request,
            User::query()->where('role', '!=', 'admin'),
            [
                'searchable' => ['name', 'email', 'phone'],
                'orderable' => [
                    0 => 'name',
                    1 => 'email',
                    2 => 'phone',
                ],
                'defaultOrder' => [
                    'column' => 'name',
                    'dir' => 'asc',
                ],
            ],
            function (User $user) {
                $user->actions = (new Actions([
                    'model' => $user,
                    'resource' => 'users',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                $user->name = (new UserInfo($user))->render()->render();

                return $user;
            }
        );
    }
}
