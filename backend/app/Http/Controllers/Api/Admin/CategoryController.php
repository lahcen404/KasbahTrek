<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;
use App\Interfaces\CategoryRepositoryInterface;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $categories = $this->categoryRepository->getAll();
        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryRepository->create($request->validated());

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryRepository->update($id, $request->validated());

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    public function destroy(int $id)
    {
        $this->categoryRepository->delete($id);

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
