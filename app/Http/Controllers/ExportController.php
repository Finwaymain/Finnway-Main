<?php

namespace App\Http\Controllers;

use App\Exports\GenericExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function export($type, $model)
    {
        $modelClass = 'App\\Models\\' . ucfirst($model);
        if (!class_exists($modelClass)) {
            abort(404, 'Model not found');
        }
        $fields = match ($model) {
                'UserApp','Driver'=> ['prenom','nom', 'phone', 'email', 'statut', 'creer'],
                'DispatcherUser' => ['first_name', 'last_name', 'phone', 'email', 'status', 'created_at'],
                'ParcelOrder' => ['id', 'source', 'destination', 'driver_name','user_name', 'sender_name', 'sender_phone', 'parcel_date', 'parcel_time', 'receive_date','receive_time', 'status', 'created_at'],
                'Rides' => ['id', 'depart_name', 'destination_name','driver_name','user_name', 'statut', 'creer'],
                'VehicleLocation'=>['vehicle_type','user_name', 'date_debut', 'date_fin','statut', 'creer'],
                default => [],
            };
        $relationships = match ($model) {
            'ParcelOrder', 'Rides' => ['user', 'driver'],
            'VehicleLocation'=>['user', 'rentalVehicleType'],
             default => [],
        };

        if (empty($fields)) {
            abort(400, 'Fields not defined for the selected model');
        }
        $query = $modelClass::query();
        if ($model === 'Driver' && request()->has('is_verified')) {
            $query->where('is_verified', request('is_verified'));
        }
        if (!empty($relationships)) {
            $query->with($relationships);
        }

        $data = $query->get();
        if (in_array($model, ['ParcelOrder', 'Rides'])) {
            $data = $data->map(function ($item) {
                $item->user_name = $item->user ? trim(($item->user->nom ?? '') . ' ' . ($item->user->prenom ?? '')) : null;
                $item->driver_name = $item->driver ? trim(($item->driver->nom ?? '') . ' ' . ($item->driver->prenom ?? '')) : null;
                return $item;
            })->filter(function ($item) {
                return !empty($item->user_name);
            });
        }
        if (in_array($model, ['VehicleLocation'])) {
            $data = $data->map(function ($item) {
               
                $item->user_name = $item->user ? trim(($item->user->nom ?? '') . ' ' . ($item->user->prenom ?? '')) : null;
                $item->vehicle_type = $item->rentalVehicleType ? trim(($item->rentalVehicleType->libelle ?? '')) : null;
                return $item;
            })->filter(function ($item) {
                return !empty($item->user_name) && !empty($item->vehicle_type);
            });
        }
        

        if ($type === 'excel') {
            return Excel::download(new GenericExport($data, $fields), $model . '.xlsx');
        }

        if ($type === 'csv') {
            return Excel::download(new GenericExport($data, $fields), $model . '.csv');
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('exports.pdf', ['data' => $data, 'fields' => $fields]);
            return $pdf->download($model . '.pdf');
        }

        abort(400, 'Invalid export type');
    }
}
