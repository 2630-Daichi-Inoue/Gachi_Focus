<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {

        $notifications = Notification::where('receiver_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('users.notification', compact('notifications'));
    }

    // Add a read/unread feature
    public function markAsRead(Request $request, Notification $notification)
    {
        if($notification->receiver_id !== Auth::id()){
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
