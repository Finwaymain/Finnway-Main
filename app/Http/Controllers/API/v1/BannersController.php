<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use DB;
class BannersController extends Controller
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

        $output = [];
        $banner = Banner::where('status', '=', 'yes')->get();

        if (count($banner) > 0) {
            foreach ($banner as $row) {
                $row->id = (string) $row->id;
                if ($row->image != '') {
                    if (file_exists(public_path('assets/images/banners' . '/' . $row->image))) {
                        $row->image = asset('assets/images/banners') . '/' . $row->image;
                    } else {
                        $row->image = asset('assets/images/placeholder_image.jpg');

                    }

                }
                $output[] = $row;
            }
            //$output[]=$row;
            if (! empty($output)) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'banners fetch successfully';
                $response['data'] = $output;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Error while fetch data';
            }
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }

        return response()->json($response);
    }

}
