<?php

namespace App\Http\Controllers;

use App\Models\OnBoard;
use File;
use Illuminate\Http\Request;
use Validator;
use App\Helpers\Helper;

class OnBoardingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'title') {
            $search = $request->input('search');
            $onboarding = OnBoard::where('title', 'LIKE', '%' . $search . '%')
                ->paginate(10);
        }
        else if ($request->has('search') && $request->search != '' && $request->selected_search == 'type') {
            $search = $request->input('search');
            $onboarding = OnBoard::where('type', 'LIKE', '%' . $search . '%')
                ->paginate(10);
        } else {
            $onboarding = OnBoard::paginate(10);
        }
        return view("on_board.index")->with("onboarding", $onboarding);
    }

     public function edit($id)
    {
        $onboarding = OnBoard::where('id', "=", $id)->first();
        return view("on_board.edit")->with("onboarding", $onboarding);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'description' => 'required',
            'image' => ($request->hasfile('image')) ? 'required|mimes:jpeg,jpg,png' : "",
        ], $messages = [
                'title.required' => '{{trans("lang.title_required")}}',
                'image.required' => '{{trans("lang.image_required")}}',
                'description.required' => '{{trans("lang.description_required")}}',
            ]);

        if ($validator->fails()) {
            return redirect('on-boarding/edit/' . $id)
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $onboarding = OnBoard::find($id);
        $fileName = $onboarding->image;

        $title = $request->input('title');
        $type = $request->input('type');
        $description = $request->input('description');
      
        if ($request->hasfile('image')) {
            if ($fileName && File::exists(public_path() . '/assets/images/onboarding/' . $fileName)) {
                File::delete(public_path() . '/assets/images/onboarding/' . $fileName);
            }
            $file = $request->file('image');
            $fileName = time() . "_" . $file->getClientOriginalName();
            $destinationPath = public_path() . '/assets/images/onboarding';
            $file->move($destinationPath, $fileName);
        }

        if ($onboarding) {
            $onboarding->title = $title;
            $onboarding->description = $description;
            $onboarding->type = $type;
            $onboarding->image = $fileName;
            $onboarding->save();
        }

        return redirect('on-boarding')->with('message', trans('lang.on_boarding_updated'));
    }

}
