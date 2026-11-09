<?php

namespace App\Http\Controllers;
use App\Models\Zone;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;

class ZoneController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index( Request $request)
    {
      if ($request->has('search') && $request->search != '' && $request->selected_search=='name') {
          $search = $request->input('search');
          $zones = Zone::where('zones.name','LIKE','%'.$search.'%')->paginate(20);
        } else{
          $zones = Zone::paginate(20);
        }
        
        return view("zone.index")->with("zones",$zones);
      }

      public function create(){
        
          $settings = Settings::first();
          $lat_long = $this->getDefaultLatLong();
          return view("zone.create")->with("settings", $settings)->with("lat_long", $lat_long);
      }

      public function edit(Request $request, $id)
      {
          $zone = Zone::find($id);
          if (!$zone) {
              return redirect('zone')->with('error', 'Zone not found');
          }
          $settings = Settings::first();
          $lat_long = $this->getDefaultLatLong();

          $coordinates = [];
          $default_lat = $zone->latitude ?? '23.022505';
          $default_lng = $zone->longitude ?? '72.571365';

          if ($zone->area) {
              $area = $zone->area->toArray();
              if (isset($area['coordinates']) && is_array($area['coordinates'])) {
                  foreach($area['coordinates'] as $key => $data){
                        foreach($data as $k=>$v){
                            $coordinates[$key][] = array('lat' => $v[1], 'lng' => $v[0]);
                        }
                  }
                  if (!empty($coordinates[0][0]['lat'])) {
                      $default_lat = $coordinates[0][0]['lat'];
                      $default_lng = $coordinates[0][0]['lng'];
                  }
              }
          }

          return view("zone.edit")
            ->with("zone",$zone)
            ->with("settings", $settings)
            ->with("lat_long", $lat_long)
            ->with("coordinates", json_encode($coordinates))
            ->with("default_lat", $default_lat)
            ->with("default_lng", $default_lng);
      }

      public function delete($id)
      {
          if ($id != "") {
              $id = json_decode($id);
              if (is_array($id)) {
                  for ($i = 0; $i < count($id); $i++) {
                      $zone = Zone::find($id[$i]);
                      if ($zone) {
                          $zone->delete();
                      }
                  }
              } else {
                  $zone = Zone::find($id);
                  if ($zone) {
                      $zone->delete();
                  }
              }
          }
          return redirect()->back()->with('success', 'Zone deleted successfully.');
    }

    public function update($id,Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required|string|max:255|unique:zones,name,' . $id,
            'coordinates' => 'required',
        ], $messages = [
            'name.required' => 'The Name field is required',
            'name.unique' => 'Another zone with this name already exists.',
            'coordinates.required' => 'Please select your zone from map',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(['message' => $messages])->withInput();
        }

        $name = trim((string) $request->input('name'));
        $status = $request->input('status') ? 'yes' : 'no';
        $coordinates = json_decode($request->input('coordinates'));

        if (!is_array($coordinates) || empty($coordinates[0]) || !is_array($coordinates[0]) || count($coordinates[0]) < 3) {
            return redirect()->back()
                ->withErrors(['coordinates' => 'Please draw a valid zone polygon with at least 3 points on the map.'])
                ->withInput();
        }

        $points = array();
        foreach ($coordinates[0] as $coordinate) {
            if (isset($coordinate->lat, $coordinate->lng)) {
                $points[] = new Point((float) $coordinate->lat, (float) $coordinate->lng);
            }
        }
        if (count($points) < 3) {
            return redirect()->back()
                ->withErrors(['coordinates' => 'Please draw a valid zone polygon with at least 3 points on the map.'])
                ->withInput();
        }
        array_push($points, $points[0]);

        $zone = Zone::find($id);
        if (!$zone) {
            return redirect('zone')->with('error', 'Zone not found.');
        }

        $zone->name = $name;
        $zone->status = $status;
        $zone->latitude = $points[0]->latitude;
        $zone->longitude = $points[0]->longitude;
        $zone->area = new Polygon([
            new LineString($points),
        ]);
        $zone->update();
        
        return redirect('zone')->with('success', "Zone '{$name}' updated successfully.");
    }

    public function store(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required|string|max:255',
            'coordinates' => 'required',
        ], $messages = [
            'name.required' => 'The Name field is required',
            'coordinates.required' => 'Please select your zone from map',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(['message' => $messages])->withInput();
        }

        $name = trim((string) $request->input('name'));
        $status = $request->input('status') ? 'yes' : 'no';
        $coordinates = json_decode($request->input('coordinates'));

        if (!is_array($coordinates) || empty($coordinates[0]) || !is_array($coordinates[0]) || count($coordinates[0]) < 3) {
            return redirect()->back()
                ->withErrors(['coordinates' => 'Please draw a valid zone polygon with at least 3 points on the map.'])
                ->withInput();
        }

        $points = array();
        foreach ($coordinates[0] as $coordinate) {
            if (isset($coordinate->lat, $coordinate->lng)) {
                $points[] = new Point((float) $coordinate->lat, (float) $coordinate->lng);
            }
        }
        if (count($points) < 3) {
            return redirect()->back()
                ->withErrors(['coordinates' => 'Please draw a valid zone polygon with at least 3 points on the map.'])
                ->withInput();
        }
        array_push($points, $points[0]);

        $polygon = new Polygon([
            new LineString($points),
        ]);

        // Check if a zone with the same name already exists (prevents UniqueConstraintViolationException on zones.zones_name_unique)
        $existingZone = Zone::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
        if ($existingZone) {
            $existingZone->name = $name;
            $existingZone->status = $status;
            $existingZone->latitude = $points[0]->latitude;
            $existingZone->longitude = $points[0]->longitude;
            $existingZone->area = $polygon;
            $existingZone->save();

            return redirect('zone')->with('success', "Zone '{$name}' already existed and has been updated with the new area.");
        }

        Zone::create([
            'name' => $name,
            'status' => $status,
            'latitude' => $points[0]->latitude,
            'longitude' => $points[0]->longitude,
            'area' => $polygon,
        ]);

        return redirect('zone')->with('success', "Zone '{$name}' created successfully.");
    }
    
    public function toggalSwitch(Request $request){
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $zone = Zone::find($id);
        if ($zone) {
            $zone->status = ($ischeck == "true") ? 'yes' : 'no';
            $zone->save();
        }
    }

    public function getDefaultLatLong(){
        $sql = DB::table('tj_settings')->select('tj_settings.contact_us_address as address', 'tj_settings.google_map_api_key as apikey')->first();
        $address = $sql->address ?? '';
        $apiKey = $sql->apikey ?? '';
        $latlong = array();

        if (!empty($address) && !empty($apiKey)) {
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 3]]);
                $raw = @file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $apiKey, false, $ctx);
                if ($raw) {
                    $geo = json_decode($raw, true);
                    if (isset($geo['status']) && $geo['status'] == 'OK' && !empty($geo['results'][0]['geometry']['location'])) {
                        $latitude = $geo['results'][0]['geometry']['location']['lat'];
                        $longitude = $geo['results'][0]['geometry']['location']['lng'];
                        $latlong = array('lat' => $latitude, 'lng' => $longitude);
                    }
                }
            } catch (\Throwable $e) {
                $latlong = array();
            }
        }
        return $latlong;
    }

}