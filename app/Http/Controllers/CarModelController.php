<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleType;
use Validator;

class CarModelController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'name') {
            $search = $request->input('search');
            $carModel = DB::table('car_model')
                ->where('car_model.name', 'LIKE', '%' . $search . '%')
                ->where('car_model.deleted_at', '=', NULL)
                ->paginate(10);
        }  else {
            $carModel = CarModel::paginate(10);
        }
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();
        return view("carModel.index")->with("carModel", $carModel)->with("brand",$brand)->with('vehicleType',$vehicleType);
    }

    public function create()
    {
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();
        return view("carModel.create")->with('brand',$brand)->with('vehicleType',$vehicleType);
    }

    public function storecarmodel(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required',
            'brand' => 'required',
            'vehicle_id'=> 'required',

        ], $messages = [
            'name.required' => 'The  Name field is required!',
            'brand.required' => 'The brand field is required!',
            'vehicle_id.required' =>'The vehicle Type field is required!',
        ]);

        if ($validator->fails()) {
            return redirect('car_model/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $carModel = new CarModel;
        $carModel->name = $request->input('name');
        $carModel->brand_id = $request->input('brand');
        $carModel->vehicle_type_id = $request->input('vehicle_id');
        $carModel->status = $request->input('status') ? 'yes' : 'no';


        $carModel->created_at = date('Y-m-d H:i:s');
        $carModel->modifier = date('Y-m-d H:i:s');
        $carModel->updated_at = date('Y-m-d H:i:s');

        $carModel->save();

        return redirect('car_model');

    }


    public function edit($id)
    {
        $carModel = CarModel::where('id', "=", $id)->first();
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();

        return view("carModel.edit")->with("carModel", $carModel)->with("brand", $brand)->with('vehicleType', $vehicleType);
    }

    public function UpdateCarModel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required',
            'brand_name' => 'required',
            'vehicle_id'=> 'required',

        ], $messages = [
            'name.required' => 'The  Name field is required!',
            'brand_name.required' => 'The brand field is required!',
            'vehicle_id.required' =>'The vehicle Type field is required!',
        ]);

        if ($validator->fails()) {
            return redirect('users/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $name = $request->input('name');
        $brand = $request->input('brand_name');
        $status = $request->input('status') ? 'yes' : 'no';
        $vehicle_type = $request->input('vehicle_id');

        $carModel = CarModel::find($id);
        if ($carModel) {
            $carModel->name = $name;
            $carModel->brand_id = $brand;
            $carModel->status = $status;
            $carModel->vehicle_type_id = $vehicle_type;
            $carModel->updated_at = date('Y-m-d H:i:s');

            $carModel->save();
        }

        return redirect('car_model');
    }

    public function deleteCarModel($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $carModel = CarModel::find($id[$i]);
                    $carModel->delete();
                }

            } else {
                $carModel = CarModel::find($id);
                $carModel->delete();
            }

        }

        return redirect()->back();
    }

    public function changeStatus($id)
    {
        $carModel = CarModel::find($id);
        if ($carModel->status == 'no') {
            $carModel->status = 'yes';
        } else {
            $carModel->status = 'no';
        }

        $carModel->save();
        return redirect()->back();

    }

    public function toggalSwitch(Request $request){
            $ischeck=$request->input('ischeck');
            $id=$request->input('id');
            $carModel = CarModel::find($id);

            if($ischeck=="true"){
              $carModel->status = 'yes';
            }else{
              $carModel->status = 'no';
            }
              $carModel->save();

    }


}
