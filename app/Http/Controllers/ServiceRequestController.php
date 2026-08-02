<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

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

        $query = ServiceRequest::with(['user', 'provider'])->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
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
        $pendingMatch = ServiceRequest::where('status', 'pending')->count();
        $completed = ServiceRequest::where('status', 'completed')->count();

        return view('service_requests.index', compact('requests', 'status', 'search', 'totalBookings', 'autoAccepted', 'pendingMatch', 'completed'));
    }

    public function show($id)
    {
        $booking = ServiceRequest::with(['user', 'provider'])->find($id);
        if (!$booking) {
            return redirect()->route('service-requests.index')->with('error', 'Booking request not found.');
        }

        return view('service_requests.show', compact('booking'));
    }
}
