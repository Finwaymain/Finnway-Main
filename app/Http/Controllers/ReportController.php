<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\VehicleType;
use App\Models\Requests;
use App\Models\PaymentMethod;
use Codedge\Fpdf\Fpdf\Fpdf;
use Carbon\Carbon;
use App\Helpers\Helper;

class ReportController extends Controller
{

    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function userreport(Request $request)
    {
        return view("reports.userreport");
    }    

    public function downloadExcel(Request $request)
    {
        $status = $request->input('user_status');
        $datePreset = $request->input('date');
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $type = $request->input('type', 'csv');
        $now = Carbon::now();

        $query = UserApp::query();

        if ($status && $status !== '0') {
            $query->where('statut', $status);
        }

        if ($datePreset === 'today') {
            $query->whereDate('creer', $now->toDateString());
        } elseif ($datePreset === 'week') {
            $query->whereBetween('creer', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
        } elseif ($datePreset === 'month') {
            $query->whereBetween('creer', [$now->copy()->startOfMonth()->toDateTimeString(), $now->copy()->endOfMonth()->toDateTimeString()]);
        } elseif ($datePreset === 'year') {
            $query->whereBetween('creer', [$now->copy()->startOfYear()->toDateTimeString(), $now->copy()->endOfYear()->toDateTimeString()]);
        } elseif ($fromDate && $toDate) {
            $query->whereBetween('creer', [Carbon::parse($fromDate)->startOfDay()->toDateTimeString(), Carbon::parse($toDate)->endOfDay()->toDateTimeString()]);
        }

        $users = $query->orderBy('id', 'desc')->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'No user data found for the selected filter.');
        }

        $filename = "user_report_" . date("Ymd_His") . "." . ($type === 'pdf' ? 'pdf' : ($type === 'xls' ? 'xls' : 'csv'));

        if ($type === 'pdf') {
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->AddPage();
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C');
            $pdf->Cell(40, 7, 'First Name', 1, 0);
            $pdf->Cell(40, 7, 'Last Name', 1, 0);
            $pdf->Cell(45, 7, 'Email', 1, 0);
            $pdf->Cell(30, 7, 'Phone', 1, 0);
            $pdf->Cell(20, 7, 'Status', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);

            foreach ($users as $u) {
                $pdf->Cell(15, 6, $u->id, 1, 0, 'C');
                $pdf->Cell(40, 6, substr($u->prenom ?? '', 0, 20), 1, 0);
                $pdf->Cell(40, 6, substr($u->nom ?? '', 0, 20), 1, 0);
                $pdf->Cell(45, 6, substr(Helper::shortEmail($u->email ?? ''), 0, 25), 1, 0);
                $pdf->Cell(30, 6, $u->phone ?? '', 1, 0);
                $pdf->Cell(20, 6, $u->statut ?? '', 1, 1, 'C');
            }
            $pdf->Output('D', $filename);
            exit;
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Sr. No.', 'User ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Status', 'Wallet Balance', 'Created Date']);

        foreach ($users as $idx => $u) {
            fputcsv($fp, [
                $idx + 1,
                $u->id,
                $u->prenom ?? '',
                $u->nom ?? '',
                $u->email ?? '',
                $u->phone ?? '',
                $u->statut ?? '',
                $u->amount ?? '0.00',
                $u->creer ?? ''
            ]);
        }
        fclose($fp);
        exit;
    }

    public function driverreport(Request $request)
    {

        return view("reports.driverreport");
    }    

    public function downloadExcelDriver(Request $request)
    {
        $status = $request->input('driver_status');
        $datePreset = $request->input('date');
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $type = $request->input('type', 'csv');
        $now = Carbon::now();

        $query = Driver::query();

        if ($status && $status !== '0') {
            $query->where('statut', $status);
        }

        if ($datePreset === 'today') {
            $query->whereDate('creer', $now->toDateString());
        } elseif ($datePreset === 'week') {
            $query->whereBetween('creer', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
        } elseif ($datePreset === 'month') {
            $query->whereBetween('creer', [$now->copy()->startOfMonth()->toDateTimeString(), $now->copy()->endOfMonth()->toDateTimeString()]);
        } elseif ($datePreset === 'year') {
            $query->whereBetween('creer', [$now->copy()->startOfYear()->toDateTimeString(), $now->copy()->endOfYear()->toDateTimeString()]);
        } elseif ($fromDate && $toDate) {
            $query->whereBetween('creer', [Carbon::parse($fromDate)->startOfDay()->toDateTimeString(), Carbon::parse($toDate)->endOfDay()->toDateTimeString()]);
        }

        $drivers = $query->orderBy('id', 'desc')->get();

        if ($drivers->isEmpty()) {
            return redirect()->back()->with('error', 'No driver data found for the selected filter.');
        }

        $filename = "driver_report_" . date("Ymd_His") . "." . ($type === 'pdf' ? 'pdf' : ($type === 'xls' ? 'xls' : 'csv'));

        if ($type === 'pdf') {
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->AddPage();
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C');
            $pdf->Cell(35, 7, 'First Name', 1, 0);
            $pdf->Cell(35, 7, 'Last Name', 1, 0);
            $pdf->Cell(30, 7, 'Phone', 1, 0);
            $pdf->Cell(45, 7, 'Email', 1, 0);
            $pdf->Cell(15, 7, 'Status', 1, 0, 'C');
            $pdf->Cell(15, 7, 'Online', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);

            foreach ($drivers as $d) {
                $pdf->Cell(15, 6, $d->id, 1, 0, 'C');
                $pdf->Cell(35, 6, substr($d->prenom ?? '', 0, 18), 1, 0);
                $pdf->Cell(35, 6, substr($d->nom ?? '', 0, 18), 1, 0);
                $pdf->Cell(30, 6, $d->phone ?? '', 1, 0);
                $pdf->Cell(45, 6, substr(Helper::shortEmail($d->email ?? ''), 0, 25), 1, 0);
                $pdf->Cell(15, 6, $d->statut ?? '', 1, 0, 'C');
                $pdf->Cell(15, 6, $d->online ?? '', 1, 1, 'C');
            }
            $pdf->Output('D', $filename);
            exit;
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Sr. No.', 'Driver ID', 'First Name', 'Last Name', 'Phone', 'Email', 'Status', 'Online', 'Wallet Balance', 'Bank Name', 'Account No', 'Created Date']);

        foreach ($drivers as $idx => $d) {
            fputcsv($fp, [
                $idx + 1,
                $d->id,
                $d->prenom ?? '',
                $d->nom ?? '',
                $d->phone ?? '',
                $d->email ?? '',
                $d->statut ?? '',
                $d->online ?? '',
                $d->amount ?? '0.00',
                $d->bank_name ?? '',
                $d->account_no ?? '',
                $d->creer ?? ''
            ]);
        }
        fclose($fp);
        exit;
    }

    public function travelreport(Request $request)
    {
        $type = VehicleType::whereNull('deleted_at')->get();
        return view("reports.travelreport")->with('type', $type);
    }    

    public function downloadExcelTravel(Request $request)
    {
        $trip_status = $request->input('trip_status');
        $payment = $request->input('payment');
        $datePreset = $request->input('date');
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $type = $request->input('type', 'csv');
        $now = Carbon::now();

        $query = DB::table('tj_requete as request')
            ->leftJoin('tj_user_app as user', 'request.id_user_app', '=', 'user.id')
            ->leftJoin('tj_conducteur as driver', 'request.id_conducteur', '=', 'driver.id')
            ->leftJoin('tj_payment_method as payment', 'request.id_payment_method', '=', 'payment.id')
            ->select(
                'request.id',
                'request.statut',
                'request.statut_paiement',
                'request.depart_name',
                'request.destination_name',
                'request.distance',
                'request.montant',
                'request.creer',
                'user.prenom as user_prenom',
                'user.nom as user_nom',
                'user.phone as user_phone',
                'driver.prenom as driver_prenom',
                'driver.nom as driver_nom',
                'driver.phone as driver_phone',
                'payment.libelle as payment_method'
            );

        if ($trip_status && $trip_status !== '0') {
            $query->where('request.statut', $trip_status);
        }

        if ($payment && $payment !== '0') {
            $query->where('request.id_payment_method', $payment);
        }

        if ($datePreset === 'today') {
            $query->whereDate('request.creer', $now->toDateString());
        } elseif ($datePreset === 'week') {
            $query->whereBetween('request.creer', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
        } elseif ($datePreset === 'month') {
            $query->whereBetween('request.creer', [$now->copy()->startOfMonth()->toDateTimeString(), $now->copy()->endOfMonth()->toDateTimeString()]);
        } elseif ($datePreset === 'year') {
            $query->whereBetween('request.creer', [$now->copy()->startOfYear()->toDateTimeString(), $now->copy()->endOfYear()->toDateTimeString()]);
        } elseif ($fromDate && $toDate) {
            $query->whereBetween('request.creer', [Carbon::parse($fromDate)->startOfDay()->toDateTimeString(), Carbon::parse($toDate)->endOfDay()->toDateTimeString()]);
        }

        $rides = $query->orderBy('request.id', 'desc')->get();

        if ($rides->isEmpty()) {
            return redirect()->back()->with('error', 'No travel/ride data found for the selected filter.');
        }

        $filename = "travel_report_" . date("Ymd_His") . "." . ($type === 'pdf' ? 'pdf' : ($type === 'xls' ? 'xls' : 'csv'));

        if ($type === 'pdf') {
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->AddPage();
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C');
            $pdf->Cell(55, 7, 'Pickup', 1, 0);
            $pdf->Cell(55, 7, 'Destination', 1, 0);
            $pdf->Cell(35, 7, 'User', 1, 0);
            $pdf->Cell(35, 7, 'Driver', 1, 0);
            $pdf->Cell(25, 7, 'Status', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Payment', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Amount', 1, 1, 'R');
            $pdf->SetFont('Arial', '', 8);

            foreach ($rides as $r) {
                $pdf->Cell(15, 6, $r->id, 1, 0, 'C');
                $pdf->Cell(55, 6, substr($r->depart_name ?? '', 0, 30), 1, 0);
                $pdf->Cell(55, 6, substr($r->destination_name ?? '', 0, 30), 1, 0);
                $pdf->Cell(35, 6, substr(($r->user_prenom ?? '') . ' ' . ($r->user_nom ?? ''), 0, 20), 1, 0);
                $pdf->Cell(35, 6, substr(($r->driver_prenom ?? '') . ' ' . ($r->driver_nom ?? ''), 0, 20), 1, 0);
                $pdf->Cell(25, 6, $r->statut ?? '', 1, 0, 'C');
                $pdf->Cell(25, 6, $r->payment_method ?? 'Cash', 1, 0, 'C');
                $pdf->Cell(30, 6, 'INR ' . number_format((float)($r->montant ?? 0), 2), 1, 1, 'R');
            }
            $pdf->Output('D', $filename);
            exit;
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");

        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Sr. No.', 'Trip ID', 'User Name', 'User Phone', 'Driver Name', 'Driver Phone', 'Pickup Location', 'Dropoff Location', 'Distance', 'Trip Status', 'Payment Status', 'Payment Method', 'Total Amount', 'Created Date']);

        foreach ($rides as $idx => $r) {
            fputcsv($fp, [
                $idx + 1,
                $r->id,
                trim(($r->user_prenom ?? '') . ' ' . ($r->user_nom ?? '')),
                $r->user_phone ?? '',
                trim(($r->driver_prenom ?? '') . ' ' . ($r->driver_nom ?? '')),
                $r->driver_phone ?? '',
                $r->depart_name ?? '',
                $r->destination_name ?? '',
                $r->distance ?? '',
                $r->statut ?? '',
                $r->statut_paiement ?? '',
                $r->payment_method ?? 'Cash',
                $r->montant ?? '0.00',
                $r->creer ?? ''
            ]);
        }
        fclose($fp);
        exit;
    }
}
