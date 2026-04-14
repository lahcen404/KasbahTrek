<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get()
        );
    }
}

