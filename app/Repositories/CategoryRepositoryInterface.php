<?php

namespace App\Repositories;

use App\DTOs\CreateCategoryDTO;
use App\DTOs\UpdateCategoryDTO;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Category;

    public function create(CreateCategoryDTO $dto): Category;

    public function update(Category $category, UpdateCategoryDTO $dto): bool;

    public function delete(Category $category): bool;
}
