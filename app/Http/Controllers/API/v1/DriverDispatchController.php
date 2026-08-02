<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Requests;
use Illuminate\Http\Request;
use DB;

class DriverDispatchController extends Controller
{
    /**
     * Check and rotate driver if the current assignment has timed out.
     */
    public function checkTimeout(Request $request)
    {
        $ride_id = $request->get('ride_id');
        $force = $request->get('force', false);

        if (empty($ride_id)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Ride ID is required'
            ]);
        }

        $ride = Requests::rotateRequestIfNeeded($ride_id, $force);

        if ($ride) {
            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Dispatch timeout check executed successfully',
                'data' => $ride
            ]);
        } else {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Ride not found'
            ]);
        }
    }

    /**
     * Force-retries matching by rotating to the next available driver.
     */
    public function retryDispatch(Request $request)
    {
        $ride_id = $request->get('ride_id');

        if (empty($ride_id)) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Ride ID is required'
            ]);
        }

        // Force rotation to next driver
        $ride = Requests::rotateRequestIfNeeded($ride_id, true);

        if ($ride) {
            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Ride successfully rotated to next driver',
                'data' => $ride
            ]);
        } else {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Ride not found'
            ]);
        }
    }
}
