<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;
use App\Helpers\Helper;

class BannersController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'title') {

            $search = strtolower($request->input('search'));

            $banners =Banner::where('title', 'LIKE', '%' . $search . '%')
                ->paginate(20);
        }
         else {
            $banners = Banner::paginate(20);

        }
      
        return view("banners.index")->with("banners", $banners);
    }
    public function create()
    {
        return view("banners.create");
    }
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'description'=>'required',
            'image'=>'required|file|mimes:jpg,jpeg,png'
        ], $messages = [
                'title.required' =>  trans("lang.setting_title_error"),
                'description.required' => trans("lang.description_required"),
                'image.required' => trans("lang.image_required"),
            ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $filename = '';
        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $filename = time() . "_" . $file->getClientOriginalName();
            if (! file_exists(public_path('assets/images/banners/'))) {
                mkdir(public_path('assets/images/banners/'), 0777, true);
            }
            $destinationPath = public_path() . '/assets/images/banners';

            $compressedImage = Helper::compressFile($file->getPathName(), $destinationPath.'/'.$filename, 8);
            /*$file->move($destinationPath, $filename);*/
        }

        Banner::create([
            'title' => $request->input('title'),
            'status' => $request->input('status') ? 'yes' : 'no',
            'image' => $filename,
            'description'=> $request->input('description')

        ]);

        return redirect('banners')->with('message', trans('lang.banner_created'));

    }

    public function edit(Request $request, $id)
    {
        $banners = Banner::find($id);
        return view("banners.edit")->with("banners", $banners);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'description' => 'required',
            'image'=>'nullable|image|mimes:jpeg,png,jpg'
        ], $messages = [
            'title.required' => trans("lang.setting_title_error"),
            'description.required' => trans("lang.description_required"),
        ]);

        if ($validator->fails()) {
            return redirect('banners/edit/' . $id)
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $banner = Banner::find($id);
        $filename = $banner->image;

        $title = $request->input('title');
        $description = $request->input('description');
        $status = $request->input('status') ? 'yes' : 'no';
        if ($request->hasfile('image')) {
            if (File::exists(public_path() . '/assets/images/banners/' . $filename)) {
                File::delete(public_path() . '/assets/images/banners/' . $filename);
            }
            $file = $request->file('image');
            $filename = time() . "_" . $file->getClientOriginalName();
            $destinationPath = public_path() . '/assets/images/banners';
            
            $compressedImage = Helper::compressFile($file->getPathName(), $destinationPath.'/'.$filename, 8);
            /*$file->move($destinationPath, $filename);*/
        }

        if ($banner) {
            $banner->title = $title;
            $banner->status = $status;
            $banner->image = $filename;
            $banner->description = $description;
            $banner->save();
        }

        return redirect('banners')->with('message', trans('lang.banner_updated'));
    }


    public function delete($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $banner = Banner::find($id[$i]);
                    
                    $destination = public_path('assets/images/banners/' . $banner->image);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $banner->delete();
                }

            } else {
                $banner = Banner::find($id);
                
                $destination = public_path('assets/images/banners/' . $banner->image);
                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $banner->delete();
            }

        }

        return redirect()->back();
    }

  
    public function changeStatus($id)
    {
        $banner = Banner::find($id);
        if ($banner->statut == 'no') {
            $banner->statut = 'yes';
        } else {
            $banner->statut = 'no';
        }

        $banner->save();
        return redirect()->back();

    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $banner = Banner::find($id);

        if ($ischeck == "true") {
            $banner->status = 'yes';
        } else {
            $banner->status = 'no';
        }
        $banner->save();
    }

}
