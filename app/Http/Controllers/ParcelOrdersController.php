<?php



namespace App\Http\Controllers;



use App\Models\Complaints;

use App\Models\Note;

use App\Models\Currency;

use App\Models\ParcelOrder;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Driver;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;

use App\Helpers\Helper;

use Carbon\Carbon;


class ParcelOrdersController extends Controller

{



    public function __construct()

    {

        $this->middleware('auth');
    }



    public function all(Request $request, $id = null)

    {
        $currency = Currency::where('statut', 'yes')->first();
        $sql = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')
            ->leftjoin('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')
            ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.tip', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.id as driver_id', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.id as user_id', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom');
        if ($id != '' || $id != null) {
            $sql->where('parcel_orders.id_conducteur', '=', $id);
        }
        $totalRides = $sql->count();
        $totalNewRides = (clone $sql)->where('parcel_orders.status', 'new')->count();
        $totalOnRides = (clone $sql)->where('parcel_orders.status', 'onride')->count();
        $totalCompletedRides = (clone $sql)->where('parcel_orders.status', 'completed')->count();
        if ($request->selected_search == 'userName' && $request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $sql->where('tj_user_app.prenom', 'LIKE', '%' . $search . '%');
            $sql->orwhere('tj_user_app.nom', 'LIKE', '%' . $search . '%');
            $sql->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ",tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
        } else if ($request->selected_search == 'driverName' && $request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $sql->where('tj_conducteur.prenom', 'LIKE', '%' . $search . '%');
            $sql->orwhere('tj_conducteur.nom', 'LIKE', '%' . $search . '%');
            $sql->orWhere(DB::raw('CONCAT(tj_conducteur.prenom, " ",tj_conducteur.nom)'), 'LIKE', '%' . $search . '%');
        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {
            $search = $request->input('ride_status');
            $sql->where('parcel_orders.status', 'LIKE', '%' . $search . '%');
        }
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $sql->whereBetween('parcel_orders.created_at', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $sql->where('parcel_orders.status', 'LIKE', '%' . $status . '%');
        }
        $rides = $sql->orderBy('parcel_orders.id', 'desc')->paginate(20);

        return view("parcel_order.all",compact('rides', 'currency','id', 'totalRides', 'totalNewRides', 'totalOnRides', 'totalCompletedRides'));
    }





    public function confirmed(Request $request)

    {

        $currency = Currency::where('statut', 'yes')->first();

        if ($request->selected_search == 'userPrenom' && $request->has('search') && $request->search != '') {



            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'confirmed')

                ->where(function ($query) use ($search) {

                    $query->orwhere('tj_user_app.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_user_app.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ",tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'driverPrenom' && $request->has('search') && $request->search != '') {



            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'confirmed')

                ->where(function ($query) use ($search) {

                    $query->orWhere('tj_conducteur.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_conducteur.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_conducteur.prenom, " ",tj_conducteur.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {

            $search = $request->input('ride_status');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'confirmed')

                ->where(function ($query) use ($search) {

                    $query->orwhere('parcel_orders.status', 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else {

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'confirmed')

                ->paginate(20);
        }



        return view("parcel_order.confirmed")->with("rides", $rides)->with('currency', $currency);
    }





    public function rejected(Request $request)

    {

        $currency = Currency::where('statut', 'yes')->first();

        if ($request->selected_search == 'userPrenom' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->Where(function ($query) {

                    $query->where('parcel_orders.status', 'rejected')

                        ->orwhere('parcel_orders.status', 'canceled');
                })

                ->where(function ($query) use ($search) {

                    $query->orwhere('tj_user_app.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_user_app.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ",tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'driverPrenom' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->Where(function ($query) {

                    $query->where('parcel_orders.status', 'rejected')

                        ->orwhere('parcel_orders.status', 'canceled');
                })

                ->where(function ($query) use ($search) {

                    $query->orWhere('tj_conducteur.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_conducteur.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_conducteur.prenom, " ",tj_conducteur.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {

            $search = $request->input('ride_status');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->Where(function ($query) {

                    $query->where('parcel_orders.status', 'rejected')

                        ->orwhere('parcel_orders.status', 'canceled');
                })

                ->where(function ($query) use ($search) {

                    $query->orwhere('parcel_orders.status', 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else {

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->Where(function ($query) {

                    $query->where('parcel_orders.status', 'rejected')

                        ->orwhere('parcel_orders.status', 'canceled');
                })

                ->paginate(20);
        }

        return view("parcel_order.rejected")->with("rides", $rides)->with('currency', $currency);
    }



    public function completed(Request $request)

    {

        $currency = Currency::where('statut', 'yes')->first();

        if ($request->selected_search == 'userPrenom' && $request->has('search') && $request->search != '') {



            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'completed')

                ->where(function ($query) use ($search) {

                    $query

                        ->orwhere('tj_user_app.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_user_app.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_user_app.prenom, " ",tj_user_app.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'driverPrenom' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'completed')

                ->where(function ($query) use ($search) {

                    $query

                        ->orWhere('tj_conducteur.prenom', 'LIKE', '%' . $search . '%')

                        ->orwhere('tj_conducteur.nom', 'LIKE', '%' . $search . '%')

                        ->orWhere(DB::raw('CONCAT(tj_conducteur.prenom, " ",tj_conducteur.nom)'), 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {

            $search = $request->input('ride_status');

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'completed')

                ->where(function ($query) use ($search) {

                    $query

                        ->orwhere('parcel_orders.status', 'LIKE', '%' . $search . '%');
                })

                ->paginate(20);
        } else {

            $rides = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

                ->join('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

                ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.discount', 'parcel_orders.id_user_app', 'parcel_orders.id_conducteur', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom')

                ->orderBy('parcel_orders.created_at', 'DESC')

                ->where('parcel_orders.status', 'completed')

                ->paginate(20);
        }

        return view("parcel_order.completed")->with("rides", $rides)->with('currency', $currency);
    }





    public function deleteRide($id)

    {



        if ($id != "") {



            $id = json_decode($id);



            if (is_array($id)) {



                for ($i = 0; $i < count($id); $i++) {

                    $complaint = Complaints::where('id_parcel', $id[$i]);

                    if ($complaint) {

                        $complaint->delete();
                    }

                    $Note = Note::where('parcel_id', $id[$i]);

                    if ($Note) {

                        $Note->delete();
                    }



                    $user = ParcelOrder::find($id[$i]);



                    if ($user->parcel_image != '') {

                        $parcelImages = json_decode($user->parcel_image, true);

                        if (count($parcelImages) > 0) {

                            foreach ($parcelImages as $parcelImage) {

                                $destination = public_path('images/parcel_order/' . $parcelImage);

                                if (File::exists($destination)) {

                                    File::delete($destination);
                                }
                            }
                        }
                    }



                    $user->delete();
                }
            } else {

                $complaint = Complaints::where('id_parcel', $id);

                if ($complaint) {

                    $complaint->delete();
                }

                $Note = Note::where('parcel_id', $id);

                if ($Note) {

                    $Note->delete();
                }



                $user = ParcelOrder::find($id);



                if ($user->parcel_image != '') {

                    $parcelImages = json_decode($user->parcel_image, true);

                    if (count($parcelImages) > 0) {

                        foreach ($parcelImages as $parcelImage) {

                            $destination = public_path('images/parcel_order/' . $parcelImage);

                            if (File::exists($destination)) {

                                File::delete($destination);
                            }
                        }
                    }
                }



                $user->delete();
            }
        }



        return redirect()->back();
    }



    public function show($id)

    {



        $currency = Currency::where('statut', 'yes')->first();



        $ride = ParcelOrder::join('tj_user_app', 'parcel_orders.id_user_app', '=', 'tj_user_app.id')

            ->leftjoin('tj_conducteur', 'parcel_orders.id_conducteur', '=', 'tj_conducteur.id')

            ->join('tj_payment_method', 'parcel_orders.id_payment_method', '=', 'tj_payment_method.id')

            ->leftjoin('parcel_category', 'parcel_orders.parcel_type', '=', 'parcel_category.id')

            ->select('parcel_orders.*')

            ->addSelect('tj_conducteur.prenom as driverPrenom', 'tj_conducteur.nom as driverNom', 'tj_conducteur.phone as driver_phone', 'tj_conducteur.email as driver_email', 'tj_conducteur.photo_path as driver_photo')

            ->addSelect('tj_user_app.prenom as userPrenom', 'tj_user_app.nom as userNom', 'tj_user_app.phone as user_phone', 'tj_user_app.email as user_email', 'tj_user_app.photo_path')

            ->addSelect('tj_payment_method.libelle', 'tj_payment_method.image')

            ->addSelect('parcel_category.title')

            ->where('parcel_orders.id', $id)->first();



        $row = $ride->toArray();





        if (!empty($row['sender_phone'])) {

            $ride->sender_phone = Helper::shortNumber($row['sender_phone']);
        }



        if (!empty($row['driver_phone'])) {

            $ride->driver_phone = Helper::shortNumber($row['driver_phone']);
        }



        if (!empty($row['driver_email'])) {

            $ride->driver_email = Helper::shortEmail($row['driver_email']);
        }



        if (!empty($row['receiver_phone'])) {

            $ride->receiver_phone = Helper::shortNumber($row['receiver_phone']);
        }



        $parcel_image = [];

        if ($ride->parcel_image != '') {

            $parcelImage = json_decode($ride->parcel_image, true);



            foreach ($parcelImage as $value) {

                if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {

                    $image = asset('images/parcel_order/') . '/' . $value;
                    array_push($parcel_image, $image);
                }

                
            }
        }



        $amount = $ride->amount;

        $tax = json_decode($ride->tax, true);

        $discount = $ride->discount;

        $tip = $ride->tip;

        $totalAmount = floatval($amount) - floatval($discount);

        $totalTaxAmount = 0;

        $taxHtml = '';

        if (!empty($tax)) {

            for ($i = 0; $i < sizeof($tax); $i++) {

                $data = $tax[$i];

                if ($data['type'] == "Percentage") {

                    $taxValue = (floatval($data['value']) * $totalAmount) / 100;

                    $taxlabel = $data['libelle'];

                    $value = $data['value'] . "%";
                } else {

                    $taxValue = floatval($data['value']);

                    $taxlabel = $data['libelle'];

                    if ($currency->symbol_at_right == "true") {

                        $value = number_format($data['value'], $currency->decimal_digit) . "" . $currency->symbole;
                    } else {

                        $value = $currency->symbole . "" . number_format($data['value'], $currency->decimal_digit);
                    }
                }

                $totalTaxAmount += floatval(number_format($taxValue, $currency->decimal_digit));

                if ($currency->symbol_at_right == "true") {

                    $taxValueAmount = number_format($taxValue, $currency->decimal_digit) . "" . $currency->symbole;
                } else {

                    $taxValueAmount = $currency->symbole . "" . number_format($taxValue, $currency->decimal_digit);
                }

                $taxHtml = $taxHtml . "<tr><td class='label'>" . $taxlabel . "(" . $value . ")</td><td><span style='color:green'>+" . $taxValueAmount . "<span></td></tr>";
            }

            $totalAmount = floatval($totalAmount) + floatval($totalTaxAmount);
        }

        $totalAmount = floatval($totalAmount) + floatval($tip);

        $customer_review = DB::table('tj_note')->where('tj_note.parcel_id', $id)->select('comment', 'niveau')->get();



        $complaints = Complaints::select('title', 'description', 'user_type')->where('id_parcel', $id)->get();



        $driverRating = "0.0";

        if (!empty($ride->id_conducteur)) {

            $id_conducteur = $ride->id_conducteur;

            $driver_rating = DB::table('tj_note')

                ->select(DB::raw("COUNT(id) as ratingCount"), DB::raw("SUM(niveau) as ratingSum"))

                ->where('id_conducteur', '=', $id_conducteur)

                ->first();

            if (!empty($driver_rating)) {

                if ($driver_rating->ratingCount > 0) {

                    $driverRating = number_format(($driver_rating->ratingSum / $driver_rating->ratingCount), 1);
                }
            }
        }



        return view("parcel_order.show")->with("ride", $ride)->with("currency", $currency)

            ->with("customer_review", $customer_review)

            ->with("complaints", $complaints)

            ->with('taxHtml', $taxHtml)

            ->with('totalAmount', $totalAmount)

            ->with('driverRating', $driverRating)

            ->with('parcel_image', $parcel_image);
    }



    public function updateRide(Request $request, $id)

    {



        $rides = ParcelOrder::find($id);
        $prevoiusStatus = $rides->status;
        $driverId = $rides->id_conducteur;
        $driverData = Driver::where('id', $driverId)->first();
        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model;
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut;
        if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
            if ($request->input('order_status') == 'confirmed') {
                if ($driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders) != '-1') {
                    if (intval($driverData->subscriptionTotalOrders) <= 0) {
                        return redirect()->back()->with('message', __('lang.upgrade_plan_limit_err'));
                    } else {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $driverId)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }
            }
        }
        if ($prevoiusStatus == 'confirmed' && ($request->input('order_status') == 'canceled' || $request->input('order_status') == 'rejected')) {
            if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                if ($driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders != '-1')) {
                    $subscriptionTotalOrders = intval($driverData->subscriptionTotalOrders) + 1;
                    Driver::where('id', $driverId)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                }
            }
        }
        if ($request->input('order_status') == 'onride') {
            if ($driverData->driver_on_ride == 'yes') {
                return redirect()->back()->with('message', __('lang.one_ride_at_a_time_allow'));
            } else {
                Driver::where('id', $driverId)->update(['driver_on_ride' => 'yes']);
            }
        }
        if ($request->input('order_status') == 'completed') {
            Driver::where('id', $driverId)->update(['driver_on_ride' => 'no']);
        }
        if ($rides) {

            $rides->status = $request->input('order_status');

            $rides->save();
        }



        return redirect()->back();
    }
}
