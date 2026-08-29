<?php



namespace App\Http\Controllers;



use App\Models\Commission;
use App\Models\Settings;
use App\Models\Driver;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;



class CommissionController extends Controller

{





    public function __construct()

    {

        $this->middleware('auth');
    }



    public function index()

    {



        $commission = Commission::first();
        $subscription = Settings::select('subscription_model', 'id')->first();
        $drivers = Driver::get();
        return view("administration_tools.commission.index", compact('commission', 'subscription', 'drivers'));
    }



    public function edit($id)

    {
        $commission = Commission::find($id);
        return view("administration_tools.commission.edit")->with('commission', $commission);
    }



    public function update(Request $request, $id)

    {



        $status = $request->has('status') ? 'yes' : 'no';

        $value = $request->input('value');

        $type = $request->input('type');

        $modifier = $request->modifier = date('Y-m-d H:i:s');



        $commission = Commission::find($id);

        if ($commission) {

            $commission->statut = $status;

            $commission->value = $value;

            $commission->type = $type;

            $commission->modifier = $modifier;



            $commission->save();

            return redirect()->back()->with('success', 'Commission updated successfully!');;
        }
    }


    public function toggalSwitchSubscriptionModel(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        Settings::where('id', $id)->update(['subscription_model' => $ischeck]);
    }
    public function bulkUpdate(Request $request)
    {
        $request->validate(
            [

                'bulk_admin_commission_value' => 'required',
                'driver' =>'required_if:driver_type,custom|array|min:1',
            ]
            
        );
        $commissionData = [
            'type' => $request->input('bulk_commission_type'),
            'value' => $request->input('bulk_admin_commission_value'),
        ];
        if ($request->driver_type === 'all') {
            Driver::query()->update(['adminCommission' => $commissionData]);
        } else {
            $selectedDrivers = $request->driver;
            Driver::whereIn('id', $selectedDrivers)->update(['adminCommission' => $commissionData]);
        }
        return redirect()->back()->with('success_bulk', 'trans("lang.bulk_update_completed")');
    }
    public function show($id)

    {



        $commission = Commission::find($id);

        return view("administration_tools.commission.show")->with('commission', $commission);
    }



    public function changeStatus(Request $request, $id)

    {

        $commission = Commission::find($id);

        if ($commission->statut == 'no') {

            $commission->statut = 'yes';

            $comm = DB::table('tj_commission')->where('id', '!=', $id)->update(['statut' => 'no']);
        } else {

            $commission->statut = 'no';

            $comm = DB::table('tj_commission')->where('id', '!=', $id)->update(['statut' => 'yes']);
        }





        $commission->save();

        return redirect()->back();
    }



    public function searchCommision(Request $request)

    {

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'Name') {

            $search = $request->input('search');

            $commmision = DB::table('tj_commission')

                ->select('tj_commission.*')

                ->where('tj_commission.libelle', 'LIKE', '%' . $search . '%')

                ->paginate(10);
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'Type') {

            $search = $request->input('search');

            $commmision = DB::table('tj_commission')

                ->select('tj_commission.*')

                ->where('tj_commission.type', 'LIKE', '%' . $search . '%')

                ->paginate(10);
        } else {

            $commmision = DB::table('tj_commission')

                ->select('tj_commission.*')

                ->paginate(10);
        }

        return view('administration_tools.commission.index')->with("commissions", $commmision);
    }

    public function toggalSwitch(Request $request)
    {

        $ischeck = $request->input('ischeck');

        $id = $request->input('id');

        $commission = Commission::find($id);



        if ($ischeck == "true") {

            $commission->statut = 'yes';
        } else {

            $commission->statut = 'no';
        }

        $commission->save();
    }
}
