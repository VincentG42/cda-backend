<?php

namespace App\Repositories;

use App\DTOs\CreateCategoryDTO;
use App\DTOs\UpdateCategoryDTO;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(): Collection
    {
        return Category::all();
    }

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    public function create(CreateCategoryDTO $dto): Category
    {
        return Category::create($dto->toArray());
    }

    public function update(Category $category, UpdateCategoryDTO $dto): bool
    {
        return $category->update($dto->toArray());
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
