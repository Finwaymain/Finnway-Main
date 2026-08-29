<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\UserApp;
use App\Models\Driver;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display live chat dashboard
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'customer');
        if (!in_array($tab, ['customer', 'business'])) {
            $tab = 'customer';
        }

        $customerUnread = SupportTicket::where('user_type', 'customer')->sum('unread_admin_count');
        $businessUnread = SupportTicket::where('user_type', 'business')->sum('unread_admin_count');

        return view('support_chat.index', compact('tab', 'customerUnread', 'businessUnread'));
    }

    /**
     * AJAX fetch tickets list
     */
    public function getTickets(Request $request)
    {
        $userType = $request->query('user_type', 'customer');
        $status = $request->query('status', 'all');
        $search = trim($request->query('search', ''));

        $query = SupportTicket::where('user_type', $userType);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_phone', 'like', "%{$search}%")
                  ->orWhere('last_message', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $customerUnread = SupportTicket::where('user_type', 'customer')->sum('unread_admin_count');
        $businessUnread = SupportTicket::where('user_type', 'business')->sum('unread_admin_count');

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'counts' => [
                'customer_unread' => $customerUnread,
                'business_unread' => $businessUnread,
            ]
        ]);
    }

    /**
     * AJAX fetch messages for active ticket & mark as read
     */
    public function getMessages(Request $request, $ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);

        // Reset admin unread count
        $ticket->update(['unread_admin_count' => 0]);
        SupportMessage::where('ticket_id', $ticketId)
            ->where('sender_type', '!=', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $afterId = $request->query('after_id', 0);
        $msgQuery = SupportMessage::where('ticket_id', $ticketId);
        if ($afterId > 0) {
            $msgQuery->where('id', '>', $afterId);
        }

        $messages = $msgQuery->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'messages' => $messages,
        ]);
    }

    /**
     * Send Admin Reply & Dispatch Push Notification via FCM
     */
    public function sendReply(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'message' => 'required|string|min:1',
        ]);

        $ticket = SupportTicket::findOrFail($request->ticket_id);
        $messageText = trim($request->message);

        // 1. Create message
        $adminMessage = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id() ?? 0,
            'sender_type' => 'admin',
            'sender_name' => 'Fiinway Support',
            'message' => $messageText,
            'is_read' => false,
        ]);

        // 2. Update Ticket
        $ticket->increment('unread_user_count', 1, [
            'last_message' => $messageText,
            'last_message_at' => now(),
            'last_sender' => 'admin',
            'status' => 'active',
        ]);

        // 3. Dispatch FCM Push Notification to User / Driver
        $this->notifyUser($ticket, $messageText);

        return response()->json([
            'success' => true,
            'message' => $adminMessage,
        ]);
    }

    /**
     * Toggle status (active / resolved)
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'status' => 'required|in:active,resolved,closed',
        ]);

        $ticket = SupportTicket::findOrFail($request->ticket_id);
        $ticket->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'status' => $ticket->status,
        ]);
    }

    /**
     * Helper: send push notification to user/driver
     */
    private function notifyUser(SupportTicket $ticket, $replyText)
    {
        try {
            $fcmToken = null;
            if ($ticket->user_type === 'customer') {
                $fcmToken = DB::table('tj_user_app')->where('id', $ticket->user_id)->value('fcm_id');
            } else {
                $fcmToken = DB::table('tj_conducteur')->where('id', $ticket->user_id)->value('fcm_id');
            }

            if (!empty($fcmToken)) {
                $title = 'Fiinway Support Reply';
                $body = strlen($replyText) > 80 ? substr($replyText, 0, 77) . '...' : $replyText;

                $payload = [
                    'title' => $title,
                    'body' => $body,
                    'tag' => 'support_chat',
                    'type' => 'support_chat',
                    'ticket_id' => (string)$ticket->id,
                    'ticket_number' => (string)$ticket->ticket_number,
                ];

                GcmController::sendNotification($fcmToken, $payload);
            }
        } catch (\Throwable $e) {
            Log::error("SupportChat notifyUser error: " . $e->getMessage());
        }
    }
}
