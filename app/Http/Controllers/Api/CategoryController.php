<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all active categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->ordered()
            ->get(['id', 'name', 'icon', 'description']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
