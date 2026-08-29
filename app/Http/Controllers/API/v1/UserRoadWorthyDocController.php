<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;
use App\Helpers\Helper;

class UserRoadWorthyDocController extends Controller
{

   public function __construct()
   {
      $this->limit=20;
   }

  public function updateRoadWorthy(Request $request)
  {
        try {
            $id_user = $request->get('id_driver');
            $image = $request->file('image');
            $date_heure = date('Y-m-d H:i:s');

            if(empty($image)) {
                return response()->json(['success' => 'Failed', 'error' => 'Image Not Found']);
            }

            $targetDir = public_path('assets/images/driver');
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $extenstion = strtolower($image->getClientOriginalExtension());
            $isImage = in_array($extenstion, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            try {
                $filename = Helper::uploadToImageKit($image, '/driver/roadworthy');
            } catch (\Throwable $e) {
                \Log::warning('ImageKit upload for driver road-worthy doc failed, falling back to local: ' . $e->getMessage());
                $filename = 'Driver_Road_worthy_' . time() . '_' . rand(100, 999) . '.' . $extenstion;
                if ($isImage) {
                    try {
                        Helper::compressFile($image->getPathName(), $targetDir . '/' . $filename, 80);
                    } catch (\Throwable $ex) {
                        $image->move($targetDir, $filename);
                    }
                } else {
                    $image->move($targetDir, $filename);
                }
            }

            $updatedata = DB::update('update tj_conducteur set photo_road_worthy = ?, photo_road_worthy_path = ?, modifier = ? where id = ?', [$filename, $filename, $date_heure, $id_user]);

            if($updatedata > 0) {
                $get_user = Driver::where('id', $id_user)->first();
                if ($get_user) {
                    $row = $get_user->toArray();
                    $row['id'] = (string)$row['id'];
                    $row['photo'] = '';
                    $row['photo_nic'] = '';
                    $row['photo_car_service_book'] = '';
                    $row['photo_licence'] = '';
                    $row['photo_road_worthy'] = '';
                    $row['photo_path'] = Helper::resolveImagePath($row['photo_path']);
                    $row['photo_nic_path'] = Helper::resolveImagePath($row['photo_nic_path']);
                    $row['photo_car_service_book_path'] = Helper::resolveImagePath($row['photo_car_service_book_path']);
                    $row['photo_licence_path'] = Helper::resolveImagePath($row['photo_licence_path']);
                    $row['photo_road_worthy_path'] = Helper::resolveImagePath($row['photo_road_worthy_path']);

                    return response()->json([
                        'success' => 'Success',
                        'error' => null,
                        'message' => 'Document Updated',
                        'data' => $row
                    ]);
                }
            }

            return response()->json(['success' => 'Failed', 'error' => 'Document Not Updated']);
        } catch (\Throwable $e) {
            \Log::error('updateRoadWorthy error: ' . $e->getMessage());
            return response()->json(['success' => 'Failed', 'error' => $e->getMessage()]);
        }
  }
}
