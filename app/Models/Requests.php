<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requests extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'tj_requete';
    public $timestamps = false;
    protected $fillable = [
        'id_user_app',
        'depart_name',
        'destination_name',
        'latitude_depart',
        'longitude_depart',
        'latitude_arrivee',
        'longitude_arrivee',
        'place',
        'number_poeple',
        'distance',
        'duree',
        'montant',
        'tip_amount',
        'trajet',
        'statut',
        'statut_paiement',
        'id_conducteur',
        'id_type_vehicule',
        'id_payment_method',
        'creer',
        'modifier',
        'date_retour',
        'heure_retour',
        'statut_round',
        'statut_course',
        'id_conducteur_accepter',
        'trip_objective',
        'trip_category',
        'age_children1',
        'age_children2',
        'age_children3',
        'feel_safe',
        'feel_safe_driver',
        'car_driver_confirmed',
        'admin_commission',
        'rejected_driver_id',   // needed for round-robin driver rotation
        'otp',
        'otp_created',
    ];
    protected $casts = [
        'id' => 'string',
    ];

    public static function rotateRequestIfNeeded($rideId, $force = false)
    {
        $ride = self::find($rideId);
        if (!$ride || (!$force && $ride->statut !== 'new')) {
            return $ride;
        }

        $settig_data = \DB::table('tj_settings')->select('trip_accept_reject_driver_time_sec')->first();
        // Allow at least 30 seconds per driver attempt (aligning with CallKit ring duration)
        $trip_accept_reject_driver_time_sec = max((int)($settig_data->trip_accept_reject_driver_time_sec ?? 30), 30);

        $currentDateTime = \Carbon\Carbon::now();
        $lastUpdateTime = $ride->modifier ?: $ride->creer;
        $expiryTime = date("Y-m-d H:i:s", strtotime("+$trip_accept_reject_driver_time_sec seconds", strtotime($lastUpdateTime)));

        // Max ride search duration: 180 seconds (3 full minutes)
        $maxRideSearchSeconds = 180;
        $rideAgeSeconds = $currentDateTime->diffInSeconds(\Carbon\Carbon::parse($ride->creer ?: $ride->modifier));

        if ($force || $currentDateTime > $expiryTime) {
            $currentDriverId = $ride->id_conducteur;
            $rejectDriverIds = [];
            if ($ride->rejected_driver_id) {
                $rejectDriverIds = json_decode($ride->rejected_driver_id, true) ?: [];
            }
            if (!empty($currentDriverId) && (int)$currentDriverId > 0 && !in_array((string)$currentDriverId, $rejectDriverIds)) {
                $rejectDriverIds[] = (string)$currentDriverId;
            }

            $lat = $ride->latitude_depart;
            $long = $ride->longitude_depart;
            $id_type_vehicule = $ride->id_type_vehicule;

            $settings = \DB::table('tj_settings')->select('driver_radios', 'minimum_deposit_amount')->first();
            $radius = $settings->driver_radios ?? 10;
            $minimum_wallet_balance = $settings->minimum_deposit_amount ?? 0;

            $libelle = \DB::table('tj_type_vehicule')->where('id', $id_type_vehicule)->value('libelle');
            $typeIds = $libelle ? \DB::table('tj_type_vehicule')->where('libelle', '=', $libelle)->pluck('id')->toArray() : [$id_type_vehicule];

            $findDriver = function($searchRadius, $excludedIds) use ($lat, $long, $typeIds) {
                return \DB::table("tj_conducteur")
                    ->join('tj_vehicule', 'tj_vehicule.id_conducteur', '=', 'tj_conducteur.id')
                    ->select(
                        "tj_conducteur.id",
                        "tj_conducteur.fcm_id",
                        \DB::raw("6371 * acos(GREATEST(-1, LEAST(1,
                            cos(radians(" . floatval($lat) . "))
                            * cos(radians(tj_conducteur.latitude))
                            * cos(radians(tj_conducteur.longitude) - radians(" . floatval($long) . "))
                            + sin(radians(" . floatval($lat) . "))
                            * sin(radians(tj_conducteur.latitude))))) AS distance")
                    )
                    ->having('distance', '<=', $searchRadius)
                    ->where('tj_conducteur.statut', 'yes')
                    ->where('tj_conducteur.online', '!=', 'no')
                    ->where('tj_conducteur.is_verified', '=', '1')
                    ->where(function($q) {
                        $q->whereNull('tj_conducteur.driver_on_ride')
                          ->orWhere('tj_conducteur.driver_on_ride', '!=', 'yes');
                    })
                    ->whereIn('tj_vehicule.id_type_vehicule', $typeIds)
                    ->when(!empty($excludedIds), function($q) use ($excludedIds) {
                        return $q->whereNotIn('tj_conducteur.id', $excludedIds);
                    })
                    ->orderBy('distance', 'asc')
                    ->first();
            };

            // Attempt 1: Search within normal radius excluding tried drivers
            $nextDriver = $findDriver($radius, $rejectDriverIds);

            // Attempt 2: If none found, expand radius up to 25km
            if (!$nextDriver) {
                $expandedRadius = max($radius * 2, 25);
                $nextDriver = $findDriver($expandedRadius, $rejectDriverIds);
            }

            // Attempt 3: If still none found, but ride search window is active (< 180s),
            // cycle back: clear exclusion list and re-alert available online drivers
            if (!$nextDriver && $rideAgeSeconds < $maxRideSearchSeconds) {
                $rejectDriverIds = [];
                $nextDriver = $findDriver($radius, []);
                if (!$nextDriver) {
                    $expandedRadius = max($radius * 2, 25);
                    $nextDriver = $findDriver($expandedRadius, []);
                }
            }

            $date_heure = date('Y-m-d H:i:s');
            if ($nextDriver) {
                $ride->id_conducteur = $nextDriver->id;
                $ride->statut = 'new';
                if (!in_array((string)$nextDriver->id, $rejectDriverIds)) {
                    $rejectDriverIds[] = (string)$nextDriver->id;
                }
                $ride->rejected_driver_id = json_encode($rejectDriverIds);
                $ride->modifier = $date_heure;
                $ride->save();

                $title = "New ride";
                $msg = "You have just received a request from a client";
                $message = array(
                    "body" => $msg,
                    "title" => $title,
                    "sound" => "mySound",
                    "tag" => "ridenewrider",
                    "statut" => "new"
                );
                
                $rideArray = $ride->toArray();
                $rideArray['id'] = (string)$rideArray['id'];
                $message = array_merge($rideArray, $message);

                if (!empty($nextDriver->fcm_id)) {
                    \App\Http\Controllers\API\v1\GcmController::sendNotification($nextDriver->fcm_id, $message);
                }
            } else {
                // If ride age is still within the 3-minute window, keep status 'new' so polling continues searching
                if ($rideAgeSeconds < $maxRideSearchSeconds) {
                    $ride->id_conducteur = 0;
                    $ride->statut = 'new';
                    $ride->modifier = $date_heure;
                    $ride->save();
                } else {
                    // Maximum search window expired: mark rejected and notify rider
                    $ride->statut = 'driver_rejected';
                    $ride->rejected_driver_id = json_encode($rejectDriverIds);
                    $ride->modifier = $date_heure;
                    $ride->save();

                    $title = "No Drivers Available";
                    $msg = "No drivers accepted your ride request. Please try again.";
                    $message = array(
                        "body" => $msg,
                        "title" => $title,
                        "sound" => "mySound",
                        "tag" => "riderejected",
                        "statut" => "driver_rejected"
                    );
                    
                    $rideArray = $ride->toArray();
                    $rideArray['id'] = (string)$rideArray['id'];
                    $message = array_merge($rideArray, $message);

                    $fcm_token = \DB::table('tj_user_app')->where('id', $ride->id_user_app)->value('fcm_id');
                    if (!empty($fcm_token)) {
                        \App\Http\Controllers\API\v1\GcmController::sendNotification($fcm_token, $message);
                    }
                }
            }
        }

        return $ride;
    }
}
