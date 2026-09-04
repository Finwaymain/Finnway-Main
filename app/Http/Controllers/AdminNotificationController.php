<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\AdminNotification;
use App\Http\Controllers\GcmController;
use App\Services\AdminNotificationService;
use Validator;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');
        $search = $request->input('search', '');
        $page = max(1, (int)$request->input('page', 1));

        $counts = AdminNotificationService::getCounts();
        $notificationData = AdminNotificationService::getNotifications($tab, $search, 15, $page);

        return view("admin_notifications.index", [
            'notifications' => $notificationData['items'],
            'pagination'    => $notificationData,
            'counts'        => $counts,
            'currentTab'    => $tab,
            'search'        => $search,
        ]);
    }

    public function create()
    {
        return view("admin_notifications.send");
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'message' => 'required',
            'send_to' => 'required',
        ], [
            'send_to.required' => 'Please choose customer or driver or both to send notification.'
        ]);

        if ($validator->fails()) {
            return redirect('notification/create')->withErrors($validator)->withInput();
        }

        $title = $request->input('title');
        $message = $request->input('message');
        $send_to = $request->input('send_to');

        // 1. Broadcast to registered customers topic (single broadcast delivery)
        if (in_array('customer', $send_to)) {
            GcmController::sendNotification('', array("body" => $message, "title" => $title), 'cabme_customer');
        }

        // 2. Broadcast to driver users topic (single broadcast delivery)
        if (in_array('driver', $send_to)) {
            GcmController::sendNotification('', array("body" => $message, "title" => $title), 'cabme_driver');
        }

        AdminNotification::insert([
            'title'      => $title,
            'message'    => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect("notification?tab=broadcast")->with('message', 'Notification successfully sent');
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);

            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $user = AdminNotification::find($id[$i]);
                    if ($user) {
                        $user->delete();
                    }
                }
                return redirect('notification?tab=broadcast')->with('message', 'Notification successfully deleted');
            } else {
                $user = AdminNotification::find($id);
                if ($user) {
                    $user->delete();
                }
                return redirect('notification?tab=broadcast')->with('message', 'Notification successfully deleted');
            }
        }

        return redirect('notification');
    }
}