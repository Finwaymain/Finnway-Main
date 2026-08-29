<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Commission;
use Illuminate\Http\Request;
use DB;
class termsofConditionController extends Controller
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
    $row = DB::table('tj_terms_and_conditions')->first();

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
        'terms' => '<p>Terms and conditions will be updated soon.</p>',
      ],
    ]);
  }

}
