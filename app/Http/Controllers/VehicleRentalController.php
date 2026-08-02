<?php



namespace App\Http\Controllers;

use App\Models\Currency;



use App\Models\VehicleLocation;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Helpers\Helper;

use Carbon\Carbon;


class VehicleRentalController extends Controller

{





    public function vehicleRent(Request $request)

    {
        $sql= DB::table('tj_location_vehicule')
            ->join('tj_type_vehicule_rental', 'tj_type_vehicule_rental.id', '=', 'tj_location_vehicule.id_vehicule_rental')
            ->join('tj_user_app', 'tj_user_app.id', '=', 'tj_location_vehicule.id_user_app')
            ->select('tj_location_vehicule.*', 'tj_type_vehicule_rental.libelle', 'tj_user_app.prenom');
        
        $totalRides = $sql->count();
        $totalNewRides = (clone $sql)->where('tj_location_vehicule.statut', 'new')->count();
        $totalOnRides = (clone $sql)->where('tj_location_vehicule.statut', 'in progress')->count();
        $totalCompletedRides = (clone $sql)->where('tj_location_vehicule.statut', 'completed')->count();    
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'vehicle_type') {
            $search = $request->input('search');
            $sql->where('tj_type_vehicule_rental.libelle', 'LIKE', '%'.$search.'%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'customer') {

            $search = $request->input('search');
            $sql->where('tj_user_app.prenom', 'LIKE', '%'.$search.'%');            
        }
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

            $sql->whereBetween('tj_location_vehicule.creer', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $sql->where('tj_location_vehicule.statut', 'LIKE', '%' . $status . '%');
        }
        $rentals=$sql->orderBy('tj_location_vehicule.creer','desc')->paginate(10);
        

        return view("vehicle.vehicle-rent",compact('rentals', 'totalRides', 'totalNewRides' ,'totalOnRides', 'totalCompletedRides'));

    }



    public function delete($id)

    {



        if ($id != "") {



            $id = json_decode($id);



            if (is_array($id)) {



                for ($i = 0; $i < count($id); $i++) {

                    $user = VehicleLocation::find($id[$i]);

                    $user->delete();

                }



            } else {

                $user = VehicleLocation::find($id);

                $user->delete();

            }



        }



        return redirect()->back();

    }



    public function show($id)

    {

        DB::enableQueryLog();

        

        $rentals = DB::table('tj_location_vehicule')

            ->join('tj_type_vehicule_rental', 'tj_type_vehicule_rental.id', '=', 'tj_location_vehicule.id_vehicule_rental')

            ->join('tj_user_app', 'tj_user_app.id', '=', 'tj_location_vehicule.id_user_app')

            ->select('tj_type_vehicule_rental.libelle', 'tj_type_vehicule_rental.image', 'tj_location_vehicule.*','tj_type_vehicule_rental.prix')

            ->addSelect('tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'tj_user_app.phone as user_phone', 'tj_user_app.email as user_email')

            ->where('tj_location_vehicule.id', $id)->first();



        $row = (array)$rentals;



        if (!empty($row['user_email'])) { 

            $rentals->user_email = Helper::shortEmail($row['user_email']);

        } 

        if (!empty($row['user_phone'])) { 

            $rentals->user_phone = Helper::shortNumber($row['user_phone']);

        }

            $currency = Currency::where('statut', 'yes')->first();

        return view("vehicle.show")->with("rentals", $rentals)->with("currency", $currency);

    }





    public function ChangeStatus(Request $request,$id)

    {

        $status = $request->input('statut');

        $user = VehicleLocation::find($id);

        if ($user) {

            $user->statut = $status;

        }

        $user->save();

        $data['data'] = 'Status updated Succesfully';

        return response()->json($data);

    }

}

