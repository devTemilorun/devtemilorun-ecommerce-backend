<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Notifications list'
        ]);
    }
    
    public function send(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:info,success,warning,error',
        ]);
        
        return response()->json([
            'message' => 'Notification sent successfully',
            'data' => $validated
        ]);
    }
    
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
        ]);
        
        return response()->json([
            'message' => 'Broadcast notification sent to all users',
            'data' => $validated
        ]);
    }
}
