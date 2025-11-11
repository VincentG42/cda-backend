<?php

namespace App\Services;

use App\DTOs\CreateCategoryDTO;
use App\DTOs\UpdateCategoryDTO;
use App\Models\Category;
use App\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function createCategory(CreateCategoryDTO $dto): Category
    {
        return $this->categoryRepository->create($dto);
    }

    public function updateCategory(Category $category, UpdateCategoryDTO $dto): bool
    {
        return $this->categoryRepository->update($category, $dto);
    }

    public function deleteCategory(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
    }
}
