<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $thresholdMinutes = 5; // 5-minute search threshold limit (like Rapido / Ola)
        $thresholdTime = Carbon::now()->subMinutes($thresholdMinutes);

        // Automatically cancel pending requests where no provider accepted within 5 minutes
        ServiceRequest::where('status', 'pending')
            ->whereNull('driver_id')
            ->where('created_at', '<', $thresholdTime)
            ->update(['status' => 'cancelled']);

        $query = ServiceRequest::with(['user', 'provider'])->orderBy('created_at', 'desc');

        if ($status === 'timed_out' || $status === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($status === 'pending') {
            $query->where('status', 'pending')
                  ->where('created_at', '>=', $thresholdTime);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('service_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('nom', 'LIKE', "%{$search}%")
                                ->orWhere('prenom', 'LIKE', "%{$search}%")
                                ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $requests = $query->paginate(15);
        $totalBookings = ServiceRequest::count();
        $autoAccepted = ServiceRequest::whereIn('status', ['accepted', 'in_progress', 'completed'])->count();
        $pendingMatch = ServiceRequest::where('status', 'pending')->whereNull('driver_id')->where('created_at', '>=', $thresholdTime)->count();
        $timedOutCount = ServiceRequest::where('status', 'cancelled')->whereNull('driver_id')->count();
        $completed = ServiceRequest::where('status', 'completed')->count();

        return view('service_requests.index', compact('requests', 'status', 'search', 'totalBookings', 'autoAccepted', 'pendingMatch', 'timedOutCount', 'completed', 'thresholdMinutes'));
    }

    public function show($id)
    {
        $booking = ServiceRequest::with(['user', 'provider'])->find($id);
        if (!$booking) {
            return redirect()->route('service_requests')->with('error', 'Booking request not found.');
        }

        return view('service_requests.show', compact('booking'));
    }

    public function retrySearch($id)
    {
        $booking = ServiceRequest::find($id);
        if ($booking) {
            $booking->status = 'pending';
            $booking->driver_id = null;
            $booking->created_at = Carbon::now();
            $booking->save();
            return redirect()->back()->with('success', 'Search restarted with a fresh 5-minute threshold window.');
        }
        return redirect()->back()->with('error', 'Service request not found.');
    }

    public function cancelRequest($id)
    {
        $booking = ServiceRequest::find($id);
        if ($booking) {
            $booking->status = 'cancelled';
            $booking->save();
            return redirect()->back()->with('success', 'Service request marked as cancelled.');
        }
        return redirect()->back()->with('error', 'Service request not found.');
    }
}
