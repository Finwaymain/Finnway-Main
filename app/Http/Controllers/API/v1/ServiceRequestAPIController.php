<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\UserApp;

class ServiceRequestAPIController extends Controller
{
    public function bookService(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');
        
        $mediaUrls = [];

        if ($request->has('media') && is_array($request->input('media'))) {
            $privateKey = config('imagekit.private_key');
            if (empty($privateKey)) {
                return response()->json([
                    'success' => 'failed',
                    'message' => 'IMAGEKIT_PRIVATE_KEY is not configured.'
                ], 500);
            }

            foreach ($request->input('media') as $mediaItem) {
                if (isset($mediaItem['base64'])) {
                    $base64 = $mediaItem['base64'];
                    
                    // Extract base64 content
                    // e.g. "data:image/png;base64,iVBORw..."
                    if (strpos($base64, 'data:') === 0) {
                        $parts = explode(',', $base64);
                        if (count($parts) == 2) {
                            $base64 = $parts[1];
                        }
                    }

                    $filename = 'service_' . time() . '_' . uniqid();

                    $url = "https://upload.imagekit.io/api/v1/files/upload";

                    $postData = [
                        'file' => $base64,
                        'fileName' => $filename,
                        'folder' => '/fiinway_service_requests',
                        'useUniqueFileName' => 'true'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ':');

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        // skip on error
                    } else {
                        $response = json_decode($result, true);
                        if (isset($response['url'])) {
                            $mediaUrls[] = $response['url'];
                        }
                    }
                    curl_close($ch);
                }
            }
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user_id,
            'driver_id' => $request->input('driver_id'),
            'service_name' => $request->input('service_name'),
            'address_type' => $request->input('address_type'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
            'preferred_date' => $request->input('date'),
            'preferred_time' => $request->input('time'),
            'description' => $request->input('description'),
            'status' => 'Pending',
            'media' => json_encode($mediaUrls)
        ]);

        return response()->json([
            'success' => 'success',
            'message' => 'Service request booked successfully.',
            'data' => $serviceRequest
        ]);
    }
    
    public function getHistory(Request $request)
    {
        $user_id = $request->header('id_user') ?? $request->input('user_id');
        
        if (!$user_id) {
            return response()->json(['success' => 'error', 'message' => 'User ID is required']);
        }
        
        $requests = ServiceRequest::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => 'success',
            'data' => $requests
        ]);
    }
    
    /**
     * Generic replacement for getHomeServices(): returns the children of any
     * tj_categorie_user node (or the top-level "All Services" categories when
     * no parent_id is given), each flagged with has_children so the Flutter
     * client knows whether to drill into another category screen or open the
     * booking form directly.
     */
    public function getServiceCategories(Request $request)
    {
        $parentId = $request->input('parent_id');

        $query = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
            ->where('statut', true);

        $query = $parentId ? $query->where('parent_id', $parentId) : $query->whereNull('parent_id');

        $rows = $query->select('id', 'libelle', 'image')->get();

        $data = $rows->map(function ($row) {
            $hasChildren = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $row->id)
                ->where('statut', true)
                ->exists();

            return [
                'id' => $row->id,
                'libelle' => $row->libelle,
                'image' => $row->image,
                'has_children' => $hasChildren,
            ];
        });

        return response()->json([
            'success' => 'success',
            'data' => $data,
        ]);
    }

    public function getHomeServices(Request $request)
    {
        $parent = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('libelle', '🧹 Home Services')->first();
        if (!$parent) {
            return response()->json(['success' => 'success', 'data' => []]);
        }

        $subCategories = \Illuminate\Support\Facades\DB::table('tj_categorie_user')->where('parent_id', $parent->id)->get();
        
        $categories = [];
        $icons = [
            'Cleaner' => '🧹',
            'Electrician' => '⚡',
            'Plumber' => '🚰',
            'Carpenter' => '🔨',
            'Painter' => '🎨',
            'Pest Control' => '🐜'
        ];

        foreach ($subCategories as $sub) {
            $services = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
                ->where('parent_id', $sub->id)
                ->where('statut', true)
                ->select('libelle', 'image')
                ->get()
                ->toArray();
                
            if (count($services) > 0) {
                $categories[] = [
                    'title' => $sub->libelle,
                    'icon' => $icons[$sub->libelle] ?? '🔧',
                    'services' => $services
                ];
            }
        }
        
        return response()->json([
            'success' => 'success',
            'data' => $categories
        ]);
    }
}
