<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateCategoryDTO;
use App\DTOs\UpdateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CategoryService $categoryService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);
        $categories = $this->categoryService->getAllCategories();

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): CategoryResource
    {
        $this->authorize('create', Category::class);
        $dto = CreateCategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): CategoryResource
    {
        $this->authorize('view', $category);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): CategoryResource
    {
        $this->authorize('update', $category);
        $dto = UpdateCategoryDTO::fromRequest($request);

        if (! $dto->hasData()) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 400);
        }

        $this->categoryService->updateCategory($category, $dto);

        return new CategoryResource($category->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): Response
    {
        $this->authorize('delete', $category);
        $this->categoryService->deleteCategory($category);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
