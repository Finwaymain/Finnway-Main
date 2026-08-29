<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Commission;
use Illuminate\Http\Request;
use DB;
class privacyPolicyController extends Controller
{

   public function __construct()
   {
      $this->limit=20;   
   }
  /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
  
  public function getData(Request $request)
  {
    $row = DB::table('tj_privacy_policy')->first();

    if ($row) {
      $row->id = (string)$row->id;
      return response()->json([
        'success' => 'success',
        'error' => null,
        'message' => 'successfully',
        'data' => $row,
      ]);
    }

    return response()->json([
      'success' => 'success',
      'error' => null,
      'message' => 'successfully',
      'data' => (object)[
        'id' => '1',
        'privacy_policy' => '<p>Privacy policy will be updated soon.</p>',
      ],
    ]);
  }
  
}
