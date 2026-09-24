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
        $targetApp = $request->query('app');

        $query = Banner::where('status', '=', 'yes');

        if ($targetApp && in_array($targetApp, ['user', 'driver'])) {
            $query->where(function ($q) use ($targetApp) {
                $q->where('target_app', $targetApp)
                  ->orWhere('target_app', 'both')
                  ->orWhereNull('target_app');
            });
        }

        $banners = $query->orderBy('id', 'desc')->get();

        if (count($banners) > 0) {
            foreach ($banners as $row) {
                $row->id = (string) $row->id;
                $row->title = (string) ($row->title ?? $row->alt ?? 'Banner');
                $row->alt = (string) ($row->alt ?? $row->title ?? '');
                $row->link = (string) ($row->link ?? '');
                $row->target_app = (string) ($row->target_app ?? 'both');
                $row->description = (string) ($row->description ?? '');

                if ($row->image != '') {
                    if (file_exists(public_path('assets/images/banners' . '/' . $row->image))) {
                        $row->image = asset('assets/images/banners') . '/' . $row->image;
                    } else {
                        $row->image = asset('assets/images/placeholder_image.jpg');
                    }
                } else {
                    $row->image = asset('assets/images/placeholder_image.jpg');
                }

                $output[] = $row;
            }

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'banners fetch successfully';
            $response['data'] = $output;
        } else {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'No banners found';
            $response['data'] = [];
        }

        return response()->json($response);
    }

}
