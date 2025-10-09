<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->customNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('users.notification', compact('notifications'));
    }
}
