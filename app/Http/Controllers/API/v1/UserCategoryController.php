<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\UserCategory;
use Illuminate\Http\Request;

class UserCategoryController extends Controller
{
    /**
     * Get user categories and subcategories.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $query = UserCategory::query();

        if ($request->has('parent_only') && $request->parent_only == 'true') {
            $query->whereNull('parent_id');
        } elseif ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            // Load nested subcategories by default
            $query->with('subcategories')->whereNull('parent_id');
        }

        $categories = $query->get();

        if ($categories->count() > 0) {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'User categories fetched successfully';
            $response['data'] = $categories;
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No user categories found';
            $response['message'] = null;
        }

        return response()->json($response);
    }
}
