<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Root Home Services Parent Category
        $parent = DB::table('tj_categorie_user')->where('libelle', 'LIKE', '%Home Services%')->whereNull('parent_id')->first();
        if (!$parent) {
            $parentId = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => '🧹 Home Services',
                'statut' => 1,
                'creer' => now(),
                'modifier' => now(),
            ]);
            $parent = (object)['id' => $parentId, 'libelle' => '🧹 Home Services'];
        }

        $search = $request->get('search');

        // Query main subcategories
        $subCategoriesQuery = DB::table('tj_categorie_user')->where('parent_id', $parent->id);
        if ($search) {
            $subCategoriesQuery->where('libelle', 'LIKE', "%{$search}%");
        }
        $services = $subCategoriesQuery->get();

        foreach ($services as $sub) {
            $sub->children = DB::table('tj_categorie_user')->where('parent_id', $sub->id)->get();
        }

        $subCategories = $services;

        return view('service_requests.categories', compact('services', 'subCategories', 'parent', 'search'));
    }

    public function create()
    {
        $parent = DB::table('tj_categorie_user')->where('libelle', 'LIKE', '%Home Services%')->whereNull('parent_id')->first();
        $categories = DB::table('tj_categorie_user')->where('parent_id', $parent->id ?? 0)->get();
        return view('service_requests.create_category', compact('parent', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'libelle' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'home_service_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/home_services'), $filename);
            $imageUrl = asset('images/home_services/' . $filename);
        }

        DB::table('tj_categorie_user')->insert([
            'libelle' => $request->libelle,
            'parent_id' => $request->parent_id,
            'statut' => true,
            'image' => $imageUrl,
            'creer' => now(),
            'modifier' => now(),
        ]);

        return redirect()->route('home_services.index')->with('success', 'Home Service Category added successfully.');
    }

    public function edit($id)
    {
        $category = DB::table('tj_categorie_user')->where('id', $id)->first();
        if (!$category) {
            return redirect()->route('home_services.index')->with('error', 'Category not found.');
        }

        $parent = DB::table('tj_categorie_user')->where('libelle', 'LIKE', '%Home Services%')->whereNull('parent_id')->first();
        $categories = DB::table('tj_categorie_user')->where('parent_id', $parent->id ?? 0)->get();

        return view('service_requests.edit_category', compact('category', 'parent', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'libelle' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $category = DB::table('tj_categorie_user')->where('id', $id)->first();
        if (!$category) {
            return redirect()->route('home_services.index')->with('error', 'Category not found.');
        }

        $imageUrl = $category->image;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'home_service_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/home_services'), $filename);
            $imageUrl = asset('images/home_services/' . $filename);
        }

        DB::table('tj_categorie_user')->where('id', $id)->update([
            'libelle' => $request->libelle,
            'parent_id' => $request->parent_id,
            'image' => $imageUrl,
            'modifier' => now(),
        ]);

        return redirect()->route('home_services.index')->with('success', 'Home Service Category updated successfully.');
    }

    public function toggleStatus($id)
    {
        $category = DB::table('tj_categorie_user')->where('id', $id)->first();
        if ($category) {
            DB::table('tj_categorie_user')->where('id', $id)->update([
                'statut' => !$category->statut,
                'modifier' => now(),
            ]);
        }
        return redirect()->back()->with('success', 'Category status updated successfully.');
    }

    public function destroy($id)
    {
        // Delete sub-services first if deleting a main subcategory
        DB::table('tj_categorie_user')->where('parent_id', $id)->delete();
        DB::table('tj_categorie_user')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Home Service Category deleted successfully.');
    }
}
