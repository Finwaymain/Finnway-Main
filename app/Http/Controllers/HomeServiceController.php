<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function ensureCommissionColumnsExist()
    {
        if (Schema::hasTable('tj_categorie_user')) {
            if (!Schema::hasColumn('tj_categorie_user', 'commission_type')) {
                Schema::table('tj_categorie_user', function ($table) {
                    $table->string('commission_type', 50)->default('percentage')->nullable();
                });
            }
            if (!Schema::hasColumn('tj_categorie_user', 'commission_value')) {
                Schema::table('tj_categorie_user', function ($table) {
                    $table->decimal('commission_value', 10, 2)->default(10.00)->nullable();
                });
            }
        }
    }

    private function getRootCategory()
    {
        $parent = DB::table('tj_categorie_user')
            ->where(function($q) {
                $q->where('libelle', 'LIKE', '%Home Services%')
                  ->orWhere('libelle', 'LIKE', '%Home Service%');
            })
            ->where(function($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->first();

        if (!$parent) {
            $parentId = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => 'Home Services',
                'parent_id' => 0,
                'statut' => 1,
                'creer' => now(),
                'modifier' => now(),
            ]);
            $parent = (object)['id' => $parentId, 'libelle' => 'Home Services'];
        }

        return $parent;
    }

    public function index(Request $request)
    {
        $this->ensureCommissionColumnsExist();
        $root = $this->getRootCategory();

        $search = $request->get('search');

        // Query Parent Services (Level 1 under Root)
        $parentServicesQuery = DB::table('tj_categorie_user')->where('parent_id', $root->id);
        if ($search) {
            $parentServicesQuery->where('libelle', 'LIKE', "%{$search}%");
        }
        $parentServices = $parentServicesQuery->get();

        // Seed initial default parent services if table is empty
        if ($parentServices->isEmpty()) {
            $defaultParents = [
                ['libelle' => 'Home Cleaning', 'commission_type' => 'percentage', 'commission_value' => 10],
                ['libelle' => 'Plumbing', 'commission_type' => 'percentage', 'commission_value' => 15],
                ['libelle' => 'AC & Geyser Service', 'commission_type' => 'percentage', 'commission_value' => 12],
                ['libelle' => 'Painting', 'commission_type' => 'percentage', 'commission_value' => 15],
                ['libelle' => 'Electrical', 'commission_type' => 'percentage', 'commission_value' => 10],
            ];
            foreach ($defaultParents as $dp) {
                $pId = DB::table('tj_categorie_user')->insertGetId([
                    'libelle' => $dp['libelle'],
                    'parent_id' => $root->id,
                    'commission_type' => $dp['commission_type'],
                    'commission_value' => $dp['commission_value'],
                    'statut' => 1,
                    'creer' => now(),
                    'modifier' => now(),
                ]);

                // Add sample skills for Home Cleaning
                if ($dp['libelle'] === 'Home Cleaning') {
                    $skills = ['House Cleaning', 'Deep Cleaning', 'Sofa & Carpet Cleaning', 'Kitchen Cleaning'];
                    foreach ($skills as $sName) {
                        $sId = DB::table('tj_categorie_user')->insertGetId([
                            'libelle' => $sName,
                            'parent_id' => $pId,
                            'statut' => 1,
                            'creer' => now(),
                            'modifier' => now(),
                        ]);
                        if ($sName === 'House Cleaning') {
                            $subSkills = ['Floor Cleaning', 'Balcony Cleaning', 'Window Cleaning', 'Office Cleaning'];
                            foreach ($subSkills as $ssName) {
                                DB::table('tj_categorie_user')->insert([
                                    'libelle' => $ssName,
                                    'parent_id' => $sId,
                                    'statut' => 1,
                                    'creer' => now(),
                                    'modifier' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
            $parentServices = DB::table('tj_categorie_user')->where('parent_id', $root->id)->get();
        }

        return view('service_requests.categories', compact('parentServices', 'root', 'search'));
    }

    public function store(Request $request)
    {
        $this->ensureCommissionColumnsExist();
        $root = $this->getRootCategory();

        $request->validate([
            'libelle' => 'required|string|max:255',
            'commission_type' => 'nullable|string',
            'commission_value' => 'nullable|numeric',
        ]);

        $parentId = $request->input('parent_id', $root->id);

        DB::table('tj_categorie_user')->insert([
            'libelle' => $request->libelle,
            'parent_id' => $parentId,
            'commission_type' => $request->input('commission_type', 'percentage'),
            'commission_value' => $request->input('commission_value', 10),
            'statut' => 1,
            'creer' => now(),
            'modifier' => now(),
        ]);

        return redirect()->route('home_services.index')->with('success', 'Parent Service added successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->ensureCommissionColumnsExist();

        $request->validate([
            'libelle' => 'required|string|max:255',
            'commission_type' => 'nullable|string',
            'commission_value' => 'nullable|numeric',
        ]);

        $updateData = [
            'libelle' => $request->libelle,
            'modifier' => now(),
        ];

        if ($request->has('commission_type')) {
            $updateData['commission_type'] = $request->input('commission_type', 'percentage');
        }
        if ($request->has('commission_value')) {
            $updateData['commission_value'] = $request->input('commission_value', 10);
        }

        DB::table('tj_categorie_user')->where('id', $id)->update($updateData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $id,
                'libelle' => $request->libelle,
                'message' => 'Service category updated successfully.'
            ]);
        }

        return redirect()->route('home_services.index')->with('success', 'Parent Service updated successfully.');
    }

    // AJAX Endpoint: Fetch Skills for a Parent Service
    public function getSkills($parentId)
    {
        try {
            if (!$parentId) {
                return response()->json(['success' => false, 'message' => 'Invalid Parent ID'], 400);
            }
            $skills = DB::table('tj_categorie_user')->where('parent_id', $parentId)->get();
            foreach ($skills as $skill) {
                $skill->sub_skills_count = DB::table('tj_categorie_user')->where('parent_id', $skill->id)->count();
            }
            return response()->json(['success' => true, 'skills' => $skills]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // AJAX Endpoint: Fetch Sub Skills for a Skill Category
    public function getSubSkills($skillId)
    {
        try {
            if (!$skillId) {
                return response()->json(['success' => false, 'message' => 'Invalid Skill ID'], 400);
            }
            $subSkills = DB::table('tj_categorie_user')->where('parent_id', $skillId)->get();
            return response()->json(['success' => true, 'sub_skills' => $subSkills]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Store Skill Category (Level 2)
    public function storeSkill(Request $request)
    {
        try {
            $request->validate([
                'parent_id' => 'required',
                'libelle' => 'required|string|max:255',
            ]);

            $id = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => $request->libelle,
                'parent_id' => $request->parent_id,
                'statut' => 1,
                'creer' => now(),
                'modifier' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'id' => $id, 'libelle' => $request->libelle, 'message' => 'Skill Category added successfully.']);
            }

            return redirect()->route('home_services.index')->with('success', 'Skill Category added successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Store Sub Skill (Level 3)
    public function storeSubSkill(Request $request)
    {
        try {
            $request->validate([
                'parent_id' => 'required',
                'libelle' => 'required|string|max:255',
            ]);

            $id = DB::table('tj_categorie_user')->insertGetId([
                'libelle' => $request->libelle,
                'parent_id' => $request->parent_id,
                'statut' => 1,
                'creer' => now(),
                'modifier' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'id' => $id, 'libelle' => $request->libelle, 'message' => 'Sub Skill added successfully.']);
            }

            return redirect()->route('home_services.index')->with('success', 'Sub Skill added successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $category = DB::table('tj_categorie_user')->where('id', $id)->first();
        $newStatus = 1;
        if ($category) {
            $newStatus = $category->statut ? 0 : 1;
            DB::table('tj_categorie_user')->where('id', $id)->update([
                'statut' => $newStatus,
                'modifier' => now(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => $id,
                'statut' => $newStatus,
                'message' => 'Status updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function destroy($id)
    {
        // Delete Level 3 descendants
        $childIds = DB::table('tj_categorie_user')->where('parent_id', $id)->pluck('id');
        if ($childIds->count() > 0) {
            DB::table('tj_categorie_user')->whereIn('parent_id', $childIds)->delete();
        }
        // Delete Level 2 descendants
        DB::table('tj_categorie_user')->where('parent_id', $id)->delete();
        // Delete main item
        DB::table('tj_categorie_user')->where('id', $id)->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Service category deleted successfully.');
    }
}
