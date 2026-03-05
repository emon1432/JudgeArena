<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Datatable\ServerSideDatatable;
use App\View\Components\Actions;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data($request));
        }

        return view('admin.pages.contact-messages.index');
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.pages.contact-messages.show', compact('contactMessage'));
    }

    protected function data(Request $request): array
    {
        return ServerSideDatatable::make(
            $request,
            ContactMessage::query(),
            [
                'searchable' => ['name', 'email', 'subject', 'status'],
                'orderable' => [
                    0 => 'name',
                    1 => 'email',
                    2 => 'subject',
                    3 => 'status',
                    4 => 'created_at',
                ],
                'defaultOrder' => [
                    'column' => 'created_at',
                    'dir' => 'desc',
                ],
            ],
            function (ContactMessage $contactMessage) {
                $contactMessage->name = '<strong>' . e($contactMessage->name) . '</strong>';
                $contactMessage->email = '<a href="mailto:' . e($contactMessage->email) . '">' . e($contactMessage->email) . '</a>';
                $contactMessage->subject = e($contactMessage->subject);
                $contactMessage->status = $this->statusBadge($contactMessage->status);
                $contactMessage->received_at = $contactMessage->created_at?->format('d M, Y h:i A');
                $contactMessage->actions = (new Actions([
                    'model' => $contactMessage,
                    'resource' => 'contact-messages',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                return $contactMessage;
            }
        );
    }

    protected function statusBadge(string $status): string
    {
        $color = match ($status) {
            'new' => 'warning',
            'read' => 'info',
            'replied' => 'success',
            default => 'secondary',
        };

        return '<span class="badge bg-label-' . $color . '">' . e(ucfirst($status)) . '</span>';
    }
}
