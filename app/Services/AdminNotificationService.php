<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminNotificationService
{
    /**
     * Get associative count of all pending admin action requests
     */
    public static function getCounts(): array
    {
        $counts = [
            'all' => 0,
            'complaints' => 0,
            'withdrawals' => 0,
            'marketplace_payouts' => 0,
            'medical_claims' => 0,
            'rides' => 0,
            'services' => 0,
            'parcels' => 0,
            'driver_kyc' => 0,
            'broadcast' => 0,
            'total_pending' => 0,
        ];

        try {
            // 1. Customer Care / Complaints
            if (Schema::hasTable('tj_complaints')) {
                $counts['complaints'] = DB::table('tj_complaints')
                    ->whereIn('status', ['initiated', 'pending', 'open', 'new'])
                    ->count();
            }

            // 2. Driver / User Payout Requests
            if (Schema::hasTable('withdrawals')) {
                $counts['withdrawals'] = DB::table('withdrawals')
                    ->whereIn('statut', ['pending', '0'])
                    ->count();
            }

            // 3. Marketplace Seller Payouts
            if (Schema::hasTable('marketplace_orders')) {
                $counts['marketplace_payouts'] = DB::table('marketplace_orders')
                    ->where('payout_status', 'pending')
                    ->count();
            }

            // 4. Medical Cashback Claims
            if (Schema::hasTable('tj_medical_claims')) {
                $counts['medical_claims'] = DB::table('tj_medical_claims')
                    ->where('status', 'pending')
                    ->count();
            }

            // 5. Ride Requests (New / Unassigned / Pending)
            if (Schema::hasTable('tj_requete')) {
                $counts['rides'] = DB::table('tj_requete')
                    ->whereIn('statut', ['new', 'pending', 'confirmed'])
                    ->count();
            }

            // 6. Home Service Requests (Pending)
            if (Schema::hasTable('service_requests')) {
                $counts['services'] = DB::table('service_requests')
                    ->whereIn('status', ['Pending', 'pending', 'new'])
                    ->count();
            }

            // 7. Parcel Orders (Pending)
            if (Schema::hasTable('parcel_orders')) {
                $counts['parcels'] = DB::table('parcel_orders')
                    ->whereIn('status', ['new', 'pending'])
                    ->count();
            }

            // 8. Driver KYC / Document Approval
            if (Schema::hasTable('tj_conducteur')) {
                $counts['driver_kyc'] = DB::table('tj_conducteur')
                    ->where('is_verified', '=', 0)
                    ->whereNull('deleted_at')
                    ->count();
            }

            // 9. Broadcast Announcements Log
            if (Schema::hasTable('admin_notification')) {
                $counts['broadcast'] = DB::table('admin_notification')->count();
            }

            $counts['total_pending'] = $counts['complaints']
                + $counts['withdrawals']
                + $counts['marketplace_payouts']
                + $counts['medical_claims']
                + $counts['rides']
                + $counts['services']
                + $counts['parcels']
                + $counts['driver_kyc'];

            $counts['all'] = $counts['total_pending'] + $counts['broadcast'];

        } catch (\Throwable $e) {
            // Fail gracefully
        }

        return $counts;
    }

    /**
     * Get total pending requests count for header badge
     */
    public static function getPendingRequestsCount(): int
    {
        $counts = self::getCounts();
        return (int) ($counts['total_pending'] ?? 0);
    }

    /**
     * Fetch aggregated, unified notification items with action links
     */
    public static function getNotifications(string $category = 'all', string $search = '', int $perPage = 20, int $page = 1)
    {
        $allItems = [];
        $search = trim($search);

        try {
            // 1. Complaints / Customer Care
            if (in_array($category, ['all', 'complaints']) && Schema::hasTable('tj_complaints')) {
                $q = DB::table('tj_complaints')
                    ->leftJoin('tj_user_app', 'tj_complaints.id_user_app', '=', 'tj_user_app.id')
                    ->leftJoin('tj_conducteur', 'tj_complaints.id_conducteur', '=', 'tj_conducteur.id')
                    ->select(
                        'tj_complaints.*',
                        'tj_user_app.prenom as userName',
                        'tj_user_app.nom as userLastName',
                        'tj_user_app.phone as userPhone',
                        'tj_conducteur.prenom as driverName',
                        'tj_conducteur.nom as driverLastName',
                        'tj_conducteur.phone as driverPhone'
                    )
                    ->orderBy('tj_complaints.id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('tj_complaints.title', 'LIKE', "%{$search}%")
                            ->orWhere('tj_complaints.description', 'LIKE', "%{$search}%")
                            ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%")
                            ->orWhere('tj_conducteur.prenom', 'LIKE', "%{$search}%");
                    });
                }

                $complaints = $q->limit(100)->get();
                foreach ($complaints as $c) {
                    $sender = $c->user_type === 'driver'
                        ? trim(($c->driverName ?? '') . ' ' . ($c->driverLastName ?? ''))
                        : trim(($c->userName ?? '') . ' ' . ($c->userLastName ?? ''));
                    $phone = $c->user_type === 'driver' ? ($c->driverPhone ?? '') : ($c->userPhone ?? '');

                    $isPending = in_array(strtolower($c->status ?? ''), ['initiated', 'pending', 'open', 'new']);

                    $allItems[] = [
                        'id' => 'complaint_' . $c->id,
                        'raw_id' => $c->id,
                        'category' => 'complaints',
                        'category_label' => 'Customer Care',
                        'category_pill' => 'Support Ticket',
                        'title' => 'Support Ticket: ' . ($c->title ?: 'Customer Complaint #' . $c->id),
                        'message' => ($sender ? 'From: ' . $sender . ($phone ? ' (' . $phone . ')' : '') . ' — ' : '') . Str::limit($c->description ?? 'No details provided', 120),
                        'time' => $c->created ? Carbon::parse($c->created) : Carbon::now(),
                        'time_formatted' => $c->created ? Carbon::parse($c->created)->diffForHumans() : 'Recently',
                        'status' => ucfirst($c->status ?? 'initiated'),
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-danger' : 'badge-success',
                        'icon' => 'mdi mdi-headset',
                        'icon_bg' => '#FEE2E2',
                        'icon_color' => '#DC2626',
                        'url' => url('complaints'),
                        'action_label' => 'Handle Ticket',
                    ];
                }
            }

            // 2. Withdrawal / Payout Requests
            if (in_array($category, ['all', 'withdrawals']) && Schema::hasTable('withdrawals')) {
                $q = DB::table('withdrawals')
                    ->leftJoin('tj_conducteur', 'tj_conducteur.id', '=', 'withdrawals.id_conducteur')
                    ->leftJoin('tj_user_app', 'tj_user_app.id', '=', 'withdrawals.id_conducteur')
                    ->select(
                        'withdrawals.*',
                        DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.prenom ELSE COALESCE(tj_conducteur.prenom, tj_user_app.prenom, '') END as prenom"),
                        DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.nom ELSE COALESCE(tj_conducteur.nom, tj_user_app.nom, '') END as nom"),
                        DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.phone ELSE COALESCE(tj_conducteur.phone, tj_user_app.phone, '') END as phone"),
                        DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.bank_name ELSE COALESCE(tj_conducteur.bank_name, tj_user_app.bank_name, '') END as bank_name"),
                        DB::raw("CASE WHEN withdrawals.note LIKE '%[User]%' THEN tj_user_app.account_no ELSE COALESCE(tj_conducteur.account_no, tj_user_app.account_no, '') END as account_no")
                    )
                    ->orderBy('withdrawals.id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('withdrawals.amount', 'LIKE', "%{$search}%")
                            ->orWhere('withdrawals.note', 'LIKE', "%{$search}%")
                            ->orWhere('tj_conducteur.prenom', 'LIKE', "%{$search}%")
                            ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%");
                    });
                }

                $withdrawals = $q->limit(100)->get();
                foreach ($withdrawals as $w) {
                    $name = trim(($w->prenom ?? '') . ' ' . ($w->nom ?? '')) ?: 'Beneficiary';
                    $isPending = in_array(strtolower($w->statut ?? ''), ['pending', '0']);
                    $statusLabel = $isPending ? 'Pending Payout' : ($w->statut === 'success' || $w->statut === '1' ? 'Paid' : ucfirst($w->statut));

                    $dateStr = $w->creer ?: ($w->created_at ?: Carbon::now());

                    $allItems[] = [
                        'id' => 'payout_' . $w->id,
                        'raw_id' => $w->id,
                        'category' => 'withdrawals',
                        'category_label' => 'Payout Request',
                        'category_pill' => 'Bank Withdrawal',
                        'title' => 'Payout Request: ₹' . number_format((float)$w->amount, 2),
                        'message' => 'Requested by ' . $name . ($w->phone ? ' (' . $w->phone . ')' : '') . ($w->bank_name ? ' | Bank: ' . $w->bank_name : '') . ($w->account_no ? ' | A/C: ' . $w->account_no : ''),
                        'time' => Carbon::parse($dateStr),
                        'time_formatted' => Carbon::parse($dateStr)->diffForHumans(),
                        'status' => $statusLabel,
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-warning' : 'badge-success',
                        'icon' => 'mdi mdi-cash-multiple',
                        'icon_bg' => '#FEF3C7',
                        'icon_color' => '#D97706',
                        'url' => url('payoutRequest'),
                        'action_label' => 'Review & Transfer',
                    ];
                }
            }

            // 3. Marketplace Seller Payouts
            if (in_array($category, ['all', 'marketplace_payouts']) && Schema::hasTable('marketplace_orders')) {
                $q = DB::table('marketplace_orders')
                    ->orderBy('id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('order_number', 'LIKE', "%{$search}%")
                            ->orWhere('contact_name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
                }

                $orders = $q->limit(100)->get();
                foreach ($orders as $o) {
                    $isPending = ($o->payout_status ?? 'pending') === 'pending';
                    $payoutAmt = (float)($o->seller_payout_amount ?: $o->total_amount);

                    $allItems[] = [
                        'id' => 'marketplace_' . $o->id,
                        'raw_id' => $o->id,
                        'category' => 'marketplace_payouts',
                        'category_label' => 'Marketplace',
                        'category_pill' => 'Seller Payout',
                        'title' => 'Marketplace Payout: ₹' . number_format($payoutAmt, 2) . ' (Order #' . ($o->order_number ?: $o->id) . ')',
                        'message' => 'Delivery: ' . ucfirst($o->status ?? 'delivered') . ' | Method: ' . strtoupper($o->payment_method ?? 'UPI') . ' | Buyer: ' . ($o->contact_name ?? 'Customer') . ($o->phone ? ' (' . $o->phone . ')' : ''),
                        'time' => $o->created_at ? Carbon::parse($o->created_at) : Carbon::now(),
                        'time_formatted' => $o->created_at ? Carbon::parse($o->created_at)->diffForHumans() : 'Recently',
                        'status' => $isPending ? 'Pending Payout' : 'Released',
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-info' : 'badge-success',
                        'icon' => 'mdi mdi-storefront-outline',
                        'icon_bg' => '#DBEAFE',
                        'icon_color' => '#2563EB',
                        'url' => url('/marketplace/admin/orders'),
                        'action_label' => 'Release Payout',
                    ];
                }
            }

            // 4. Medical Cashback Claims
            if (in_array($category, ['all', 'medical_claims']) && Schema::hasTable('tj_medical_claims')) {
                $q = DB::table('tj_medical_claims')
                    ->leftJoin('tj_user_app', 'tj_medical_claims.user_id', '=', 'tj_user_app.id')
                    ->select('tj_medical_claims.*', 'tj_user_app.prenom as userName', 'tj_user_app.nom as userLastName', 'tj_user_app.phone as userPhone')
                    ->orderBy('tj_medical_claims.id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('tj_medical_claims.claim_id', 'LIKE', "%{$search}%")
                            ->orWhere('tj_medical_claims.requested_amount', 'LIKE', "%{$search}%")
                            ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%");
                    });
                }

                $claims = $q->limit(100)->get();
                foreach ($claims as $cl) {
                    $isPending = ($cl->status ?? 'pending') === 'pending';
                    $name = trim(($cl->userName ?? '') . ' ' . ($cl->userLastName ?? '')) ?: 'User';
                    $dateStr = $cl->creer ?: ($cl->created_at ?: Carbon::now());

                    $allItems[] = [
                        'id' => 'medical_' . $cl->id,
                        'raw_id' => $cl->id,
                        'category' => 'medical_claims',
                        'category_label' => 'Medical Cashback',
                        'category_pill' => 'Claim Request',
                        'title' => 'Medical Claim: ' . ($cl->claim_id ?: '#' . $cl->id) . ' (₹' . number_format((float)$cl->requested_amount, 2) . ')',
                        'message' => 'Claimant: ' . $name . ($cl->userPhone ? ' (' . $cl->userPhone . ')' : '') . ' | Status: ' . ucfirst($cl->status ?? 'pending'),
                        'time' => Carbon::parse($dateStr),
                        'time_formatted' => Carbon::parse($dateStr)->diffForHumans(),
                        'status' => ucfirst($cl->status ?? 'pending'),
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-warning' : ($cl->status === 'approved' ? 'badge-success' : 'badge-danger'),
                        'icon' => 'mdi mdi-hospital-box-outline',
                        'icon_bg' => '#D1FAE5',
                        'icon_color' => '#059669',
                        'url' => url('/admin/medical-cashback'),
                        'action_label' => 'Review Claim',
                    ];
                }
            }

            // 5. Ride Requests
            if (in_array($category, ['all', 'rides']) && Schema::hasTable('tj_requete')) {
                $q = DB::table('tj_requete')
                    ->leftJoin('tj_user_app', 'tj_requete.id_user_app', '=', 'tj_user_app.id')
                    ->select('tj_requete.*', 'tj_user_app.prenom as userName', 'tj_user_app.phone as userPhone')
                    ->orderBy('tj_requete.id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('tj_requete.id', 'LIKE', "%{$search}%")
                            ->orWhere('tj_requete.depart_name', 'LIKE', "%{$search}%")
                            ->orWhere('tj_requete.destination_name', 'LIKE', "%{$search}%")
                            ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%");
                    });
                }

                $rides = $q->limit(50)->get();
                foreach ($rides as $r) {
                    $isPending = in_array(strtolower($r->statut ?? ''), ['new', 'pending', 'confirmed']);
                    $dateStr = $r->creer ?: ($r->created_at ?: Carbon::now());

                    $allItems[] = [
                        'id' => 'ride_' . $r->id,
                        'raw_id' => $r->id,
                        'category' => 'rides',
                        'category_label' => 'Ride Booking',
                        'category_pill' => 'Cab Ride',
                        'title' => 'Ride Request #' . $r->id . ' (₹' . number_format((float)$r->montant, 2) . ')',
                        'message' => 'Passenger: ' . ($r->userName ?? 'Customer') . ($r->userPhone ? ' (' . $r->userPhone . ')' : '') . ($r->depart_name ? ' | From: ' . Str::limit($r->depart_name, 35) : '') . ($r->destination_name ? ' To: ' . Str::limit($r->destination_name, 35) : ''),
                        'time' => Carbon::parse($dateStr),
                        'time_formatted' => Carbon::parse($dateStr)->diffForHumans(),
                        'status' => ucfirst($r->statut ?? 'new'),
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-primary' : ($r->statut === 'completed' ? 'badge-success' : 'badge-secondary'),
                        'icon' => 'mdi mdi-car',
                        'icon_bg' => '#EEF2FF',
                        'icon_color' => '#4F46E5',
                        'url' => url('ride/show/' . $r->id),
                        'action_label' => 'View Ride',
                    ];
                }
            }

            // 6. Home Service Requests
            if (in_array($category, ['all', 'services']) && Schema::hasTable('service_requests')) {
                $q = DB::table('service_requests')
                    ->leftJoin('tj_user_app', 'service_requests.user_id', '=', 'tj_user_app.id')
                    ->select('service_requests.*', 'tj_user_app.prenom as userName', 'tj_user_app.phone as userPhone')
                    ->orderBy('service_requests.id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('service_requests.service_name', 'LIKE', "%{$search}%")
                            ->orWhere('service_requests.city', 'LIKE', "%{$search}%")
                            ->orWhere('tj_user_app.prenom', 'LIKE', "%{$search}%");
                    });
                }

                $services = $q->limit(50)->get();
                foreach ($services as $s) {
                    $isPending = in_array(strtolower($s->status ?? ''), ['pending', 'new']);

                    $allItems[] = [
                        'id' => 'service_' . $s->id,
                        'raw_id' => $s->id,
                        'category' => 'services',
                        'category_label' => 'Home Service',
                        'category_pill' => 'Service Booking',
                        'title' => 'Service: ' . ($s->service_name ?: 'Home Service #' . $s->id) . ' (₹' . number_format((float)$s->amount, 2) . ')',
                        'message' => 'Customer: ' . ($s->userName ?? 'Client') . ($s->userPhone ? ' (' . $s->userPhone . ')' : '') . ($s->city ? ' | City: ' . $s->city : '') . ($s->preferred_date ? ' | Scheduled: ' . $s->preferred_date : ''),
                        'time' => $s->created_at ? Carbon::parse($s->created_at) : Carbon::now(),
                        'time_formatted' => $s->created_at ? Carbon::parse($s->created_at)->diffForHumans() : 'Recently',
                        'status' => ucfirst($s->status ?? 'pending'),
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-warning' : ($s->status === 'Completed' ? 'badge-success' : 'badge-secondary'),
                        'icon' => 'mdi mdi-wrench-outline',
                        'icon_bg' => '#F3E8FF',
                        'icon_color' => '#7C3AED',
                        'url' => url('service-requests/show/' . $s->id),
                        'action_label' => 'Manage Service',
                    ];
                }
            }

            // 7. Parcel Requests
            if (in_array($category, ['all', 'parcels']) && Schema::hasTable('parcel_orders')) {
                $q = DB::table('parcel_orders')
                    ->orderBy('id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('id', 'LIKE', "%{$search}%");
                    });
                }

                $parcels = $q->limit(50)->get();
                foreach ($parcels as $p) {
                    $isPending = in_array(strtolower($p->status ?? ''), ['new', 'pending']);
                    $allItems[] = [
                        'id' => 'parcel_' . $p->id,
                        'raw_id' => $p->id,
                        'category' => 'parcels',
                        'category_label' => 'Parcel Delivery',
                        'category_pill' => 'Package Delivery',
                        'title' => 'Parcel Order #' . $p->id,
                        'message' => 'Status: ' . ucfirst($p->status ?? 'new') . ' | Delivery booking',
                        'time' => isset($p->created_at) ? Carbon::parse($p->created_at) : Carbon::now(),
                        'time_formatted' => isset($p->created_at) ? Carbon::parse($p->created_at)->diffForHumans() : 'Recently',
                        'status' => ucfirst($p->status ?? 'new'),
                        'is_pending' => $isPending,
                        'status_class' => $isPending ? 'badge-info' : 'badge-success',
                        'icon' => 'mdi mdi-package-variant-closed',
                        'icon_bg' => '#FCE7F3',
                        'icon_color' => '#DB2777',
                        'url' => url('parcel/all'),
                        'action_label' => 'View Parcel',
                    ];
                }
            }

            // 8. Driver KYC / Approvals
            if (in_array($category, ['all', 'driver_kyc']) && Schema::hasTable('tj_conducteur')) {
                $q = DB::table('tj_conducteur')
                    ->where('is_verified', '=', 0)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('prenom', 'LIKE', "%{$search}%")
                            ->orWhere('nom', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }

                $drivers = $q->limit(50)->get();
                foreach ($drivers as $d) {
                    $name = trim(($d->prenom ?? '') . ' ' . ($d->nom ?? '')) ?: 'New Driver';
                    $dateStr = $d->creer ?: ($d->created_at ?: Carbon::now());

                    $allItems[] = [
                        'id' => 'kyc_' . $d->id,
                        'raw_id' => $d->id,
                        'category' => 'driver_kyc',
                        'category_label' => 'Driver Verification',
                        'category_pill' => 'KYC & Documents',
                        'title' => 'Driver KYC Verification: ' . $name,
                        'message' => 'Phone: ' . ($d->phone ?? 'N/A') . ($d->email ? ' | Email: ' . $d->email : '') . ' | Documents uploaded and awaiting admin verification.',
                        'time' => Carbon::parse($dateStr),
                        'time_formatted' => Carbon::parse($dateStr)->diffForHumans(),
                        'status' => 'Pending Verification',
                        'is_pending' => true,
                        'status_class' => 'badge-danger',
                        'icon' => 'mdi mdi-account-check-outline',
                        'icon_bg' => '#FFEDD5',
                        'icon_color' => '#EA580C',
                        'url' => url('driver/document/view/' . $d->id),
                        'action_label' => 'Verify Documents',
                    ];
                }
            }

            // 9. Broadcast Announcements Log
            if (in_array($category, ['all', 'broadcast']) && Schema::hasTable('admin_notification')) {
                $q = DB::table('admin_notification')->orderBy('id', 'desc');

                if (!empty($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('message', 'LIKE', "%{$search}%");
                    });
                }

                $broadcasts = $q->limit(50)->get();
                foreach ($broadcasts as $b) {
                    $allItems[] = [
                        'id' => 'broadcast_' . $b->id,
                        'raw_id' => $b->id,
                        'category' => 'broadcast',
                        'category_label' => 'Broadcast Message',
                        'category_pill' => 'Push Notification',
                        'title' => 'Broadcast: ' . ($b->title ?? 'Admin Announcement'),
                        'message' => Str::limit($b->message ?? '', 140),
                        'time' => $b->created_at ? Carbon::parse($b->created_at) : Carbon::now(),
                        'time_formatted' => $b->created_at ? Carbon::parse($b->created_at)->diffForHumans() : 'Recently',
                        'status' => 'Delivered',
                        'is_pending' => false,
                        'status_class' => 'badge-dark',
                        'icon' => 'mdi mdi-bullhorn-outline',
                        'icon_bg' => '#F1F5F9',
                        'icon_color' => '#475569',
                        'url' => url('notification'),
                        'action_label' => 'Broadcast Log',
                    ];
                }
            }

        } catch (\Throwable $e) {
            // Fail gracefully
        }

        // Sort all items: pending items first, then by date descending
        usort($allItems, function ($a, $b) {
            if ($a['is_pending'] !== $b['is_pending']) {
                return $a['is_pending'] ? -1 : 1;
            }
            $tA = $a['time'] instanceof Carbon ? $a['time']->getTimestamp() : strtotime((string)$a['time']);
            $tB = $b['time'] instanceof Carbon ? $b['time']->getTimestamp() : strtotime((string)$b['time']);
            return $tB <=> $tA;
        });

        // Paginate in-memory array
        $total = count($allItems);
        $offset = ($page - 1) * $perPage;
        $slicedItems = array_slice($allItems, $offset, $perPage);

        return [
            'items' => $slicedItems,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
