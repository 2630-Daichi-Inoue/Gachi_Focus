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

    // Add a read/unread feature
    public function markAsRead(Request $request, CustomNotification $notification)
    {
        if($notification->user_id !== auth()->id()){
            abort(403);
        }

        if(is_null($notification->read_at)){
            $notification->update(['read_at' => now()]);
        }

        if($request->expectsJson()){
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }
}
