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
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpg,jpeg,png,webp',
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:2048',
            'target_app' => 'nullable|in:user,driver,both',
            'description' => 'nullable|string',
        ], [
            'image.required' => trans("lang.image_required") ?? 'Please upload a banner image',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
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
        }

        $title = $request->input('title') ?: ($request->input('alt') ?: 'Banner');
        $alt = $request->input('alt') ?: ($request->input('title') ?: '');

        Banner::create([
            'title' => $title,
            'alt' => $alt,
            'link' => $request->input('link'),
            'target_app' => $request->input('target_app', 'both') ?: 'both',
            'status' => $request->input('status') ? 'yes' : 'no',
            'image' => $filename,
            'description' => $request->input('description') ?? ''
        ]);

        return redirect('banners')->with('message', trans('lang.banner_created') ?? 'Banner created successfully');
    }

    public function edit(Request $request, $id)
    {
        $banners = Banner::find($id);
        return view("banners.edit")->with("banners", $banners);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:2048',
            'target_app' => 'nullable|in:user,driver,both',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect('banners/edit/' . $id)
                ->withErrors($validator)
                ->withInput();
        }

        $banner = Banner::find($id);
        if (!$banner) {
            return redirect('banners')->with('error', 'Banner not found');
        }

        $filename = $banner->image;

        $title = $request->input('title') ?: ($request->input('alt') ?: $banner->title);
        $alt = $request->input('alt') ?: ($request->input('title') ?: $banner->alt);
        $description = $request->input('description') ?? ($banner->description ?? '');
        $status = $request->input('status') ? 'yes' : 'no';

        if ($request->hasfile('image')) {
            if (File::exists(public_path() . '/assets/images/banners/' . $filename)) {
                File::delete(public_path() . '/assets/images/banners/' . $filename);
            }
            $file = $request->file('image');
            $filename = time() . "_" . $file->getClientOriginalName();
            $destinationPath = public_path() . '/assets/images/banners';
            
            $compressedImage = Helper::compressFile($file->getPathName(), $destinationPath.'/'.$filename, 8);
        }

        $banner->title = $title;
        $banner->alt = $alt;
        $banner->link = $request->input('link');
        $banner->target_app = $request->input('target_app', 'both') ?: 'both';
        $banner->status = $status;
        $banner->image = $filename;
        $banner->description = $description;
        $banner->save();

        return redirect('banners')->with('message', trans('lang.banner_updated') ?? 'Banner updated successfully');
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
