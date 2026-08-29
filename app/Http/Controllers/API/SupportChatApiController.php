<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportQuickQuestion;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportChatApiController extends Controller
{
    /**
     * Fetch active quick questions for user_type (customer or business)
     */
    public function getQuickQuestions(Request $request)
    {
        $userType = $request->query('user_type', 'customer');
        if (!in_array($userType, ['customer', 'business'])) {
            $userType = 'customer';
        }

        $questions = SupportQuickQuestion::active()
            ->forUserType($userType)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => 'success',
            'data' => $questions,
        ]);
    }

    /**
     * Get or create active support ticket for the user
     */
    public function getOrCreateTicket(Request $request)
    {
        $userId = $request->input('user_id');
        $userType = $request->input('user_type', 'customer');
        $topic = $request->input('topic');

        if (empty($userId)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'user_id is required',
            ], 400);
        }

        // Fetch user profile info
        $userName = 'User #' . $userId;
        $userPhone = '';
        $userEmail = '';
        $userPhoto = '';

        if ($userType === 'customer') {
            $user = UserApp::find($userId);
            if ($user) {
                $userName = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
                if (empty($userName)) $userName = 'Customer #' . $userId;
                $userPhone = $user->phone ?? '';
                $userEmail = $user->email ?? '';
                $userPhoto = $user->photo_path ?? $user->photo ?? '';
            }
        } else {
            $driver = Driver::find($userId);
            if ($driver) {
                $userName = trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? ''));
                if (empty($userName)) $userName = 'Driver #' . $userId;
                $userPhone = $driver->phone ?? '';
                $userEmail = $driver->email ?? '';
                $userPhoto = $driver->photo_path ?? $driver->photo ?? '';
            }
        }

        // Check for existing active ticket
        $ticket = SupportTicket::where('user_id', $userId)
            ->where('user_type', $userType)
            ->whereIn('status', ['active', 'resolved'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$ticket || $ticket->status === 'closed') {
            $ticketNumber = 'TIC-' . strtoupper(substr($userType, 0, 1)) . '-' . date('ymd') . '-' . rand(1000, 9999);
            $ticket = SupportTicket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => $userId,
                'user_type' => $userType,
                'user_name' => $userName,
                'user_phone' => $userPhone,
                'user_email' => $userEmail,
                'user_photo' => $userPhoto,
                'topic' => $topic ?? 'General Support',
                'status' => 'active',
                'last_sender' => 'user',
                'unread_admin_count' => 0,
                'unread_user_count' => 0,
            ]);
        } else {
            // Update profile info in case it changed
            $ticket->update([
                'user_name' => $userName,
                'user_phone' => $userPhone,
                'user_email' => $userEmail,
                'user_photo' => $userPhoto,
                'status' => 'active',
            ]);
        }

        // Reset unread_user_count
        $ticket->update(['unread_user_count' => 0]);
        SupportMessage::where('ticket_id', $ticket->id)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => 'success',
            'data' => $ticket,
        ]);
    }

    /**
     * Get messages for a ticket (supports incremental polling with after_id)
     */
    public function getMessages(Request $request)
    {
        $ticketId = $request->query('ticket_id');
        $afterId = $request->query('after_id', 0);

        if (empty($ticketId)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'ticket_id is required',
            ], 400);
        }

        $query = SupportMessage::where('ticket_id', $ticketId);

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->orderBy('id', 'asc')->get();

        // Mark user unread messages as read
        if ($messages->isNotEmpty()) {
            SupportMessage::where('ticket_id', $ticketId)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            SupportTicket::where('id', $ticketId)->update(['unread_user_count' => 0]);
        }

        $ticket = SupportTicket::find($ticketId);

        return response()->json([
            'success' => 'success',
            'ticket_status' => $ticket ? $ticket->status : 'active',
            'data' => $messages,
        ]);
    }

    /**
     * Send message from App
     */
    public function sendMessage(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $userId = $request->input('user_id');
        $userType = $request->input('user_type', 'customer');
        $messageText = trim($request->input('message', ''));
        $senderName = $request->input('sender_name', 'User');
        $questionId = $request->input('question_id'); // if sent from quick question pill

        if (empty($ticketId) || empty($messageText)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'ticket_id and message are required',
            ], 400);
        }

        $ticket = SupportTicket::find($ticketId);
        if (!$ticket) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Ticket not found',
            ], 404);
        }

        // 1. Insert user message
        $userMessage = SupportMessage::create([
            'ticket_id' => $ticketId,
            'sender_id' => $userId ?? $ticket->user_id,
            'sender_type' => $userType,
            'sender_name' => $senderName,
            'message' => $messageText,
            'is_read' => false,
        ]);

        // 2. Check if there is an automated instant reply for this quick question
        $autoReplyMsg = null;
        if (!empty($questionId)) {
            $question = SupportQuickQuestion::find($questionId);
            if ($question && !empty($question->auto_reply) && $question->status === 'active') {
                $autoReplyMsg = SupportMessage::create([
                    'ticket_id' => $ticketId,
                    'sender_id' => 0,
                    'sender_type' => 'admin',
                    'sender_name' => 'Fiinway Support Assistant',
                    'message' => $question->auto_reply,
                    'is_read' => true,
                ]);
            }
        }

        // 3. Update Ticket summary
        $lastMsg = $autoReplyMsg ? $autoReplyMsg->message : $userMessage->message;
        $ticket->increment('unread_admin_count', 1, [
            'last_message' => $lastMsg,
            'last_message_at' => now(),
            'last_sender' => $autoReplyMsg ? 'admin' : 'user',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => 'success',
            'data' => [
                'user_message' => $userMessage,
                'auto_reply' => $autoReplyMsg,
            ],
        ]);
    }

    /**
     * Close support ticket
     */
    public function closeTicket(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $ticket = SupportTicket::find($ticketId);
        if ($ticket) {
            $ticket->update(['status' => 'resolved']);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Ticket resolved successfully',
        ]);
    }
}
