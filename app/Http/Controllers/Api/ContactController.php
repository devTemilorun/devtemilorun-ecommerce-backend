<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Save to database
        $contactMessage = ContactMessage::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'user_id' => $request->user()?->id,
            'status' => 'unread',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully. We will get back to you soon!',
            'data' => $contactMessage
        ], 201);
    }

    public function getMessages(Request $request)
    {
        $query = ContactMessage::query();
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }
        
        $messages = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function getMessage($id)
    {
        $message = ContactMessage::findOrFail($id);
        
        // Only mark as read if it's unread
        if ($message->status === 'unread') {
            $message->markAsRead();
        }
        
        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    public function reply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply_message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = ContactMessage::findOrFail($id);
        
        // Store the reply in database
        $message->update([
            'status' => 'replied',
            'replied_at' => now(),
            'admin_reply' => $request->reply_message,
            'replied_by' => $request->user()?->id,
        ]);
        
        // Here you would send an email to the user
        // Mail::to($message->email)->send(new ContactReplyMail($message, $request->reply_message));

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => $message
        ]);
    }

    public function delete($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }
}