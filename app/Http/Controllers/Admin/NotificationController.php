<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = CustomNotification::where('receiver_id', auth()->id())
                ->orderByDesc('created_at')
                ->paginate(5);

        return view('admin.notification', compact('notifications'));
    }

    public function markAsRead(CustomNotification $notification)
    {
        if($notification->receiver_id !== auth()->id()){
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
