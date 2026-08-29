<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\VehicleType;
use DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function __construct()
    {
        $this->limit = 20;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getData(Request $request)
    {
        $months = array("January" => 'Jan', "February" => 'Feb', "March" => 'Mar', "April" => 'Apr', "May" => 'May', "June" => 'Jun', "July" => 'Jul', "August" => 'Aug', "September" => 'Sep', "October" => 'Oct', "November" => 'Nov', "December" => 'Dec');

        $id_user_app = $request->get('id_user_app');
        $output = [];
        $response = [
            'success' => 'Failed',
            'error' => 'No Data Found',
            'message' => null,
        ];

        if (!empty($id_user_app)) {
            $userObj = DB::table('tj_user_app')->where('id', $id_user_app)->orWhere('ac_no', $id_user_app)->first();
            $sql = DB::table('tj_transaction')
                ->where(function($q) use ($id_user_app, $userObj) {
                    $q->where('tj_transaction.id_user_app', '=', $id_user_app);
                    if ($userObj) {
                        $q->orWhere('tj_transaction.id_user_app', '=', $userObj->id);
                        if (!empty($userObj->ac_no)) {
                            $q->orWhere('tj_transaction.ac_no', '=', $userObj->ac_no);
                        }
                    }
                })
                ->orderBy('tj_transaction.id', 'desc')
                ->get();

            foreach ($sql as $row) {
                $row->creer = date("d", strtotime($row->creer)) . " " . $months[date("F", strtotime($row->creer))] . ", " . date("Y", strtotime($row->creer));
                $ride_id = $row->ride_id;
                if (!empty($ride_id)) {
                    $ride = DB::table('tj_requete')
                        ->select('tj_requete.transaction_id', 'tj_requete.date_retour')
                        ->where('id', '=', $ride_id)
                        ->get();

                    if ($ride->count() > 0) {
                        foreach ($ride as $row_ride) {
                            $row->transaction_id = $row_ride->transaction_id;
                            $row->date_retour = !empty($row_ride->date_retour) ? date("d", strtotime($row_ride->date_retour)) . " " . ($months[date("F", strtotime($row_ride->date_retour))] ?? '') . ", " . date("Y", strtotime($row_ride->date_retour)) : "";
                        }
                    } else {
                        $svc = DB::table('service_requests')->where('id', '=', $ride_id)->first();
                        if ($svc) {
                            $row->order_type = 'service';
                            $row->depart_name = $svc->service_name;
                        }
                    }
                }

                $paddedId = str_pad((string) $row->id, 7, '0', STR_PAD_LEFT);
                $row->id = $paddedId;
                if (empty($row->txn_id) || is_numeric($row->txn_id)) {
                    $row->txn_id = $paddedId;
                }
                if (empty($row->transaction_id) || is_numeric($row->transaction_id)) {
                    $row->transaction_id = $row->txn_id ?? $paddedId;
                }
                $output[] = $row;
            }

            if (count($output) > 0) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Sucessfully';
                $response['data'] = $output;
            }
        } else {
            $response['error'] = 'Id Required';
        }

        return response()->json($response);
    }

}
