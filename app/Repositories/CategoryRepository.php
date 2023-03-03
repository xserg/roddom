<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function getCategoryById(int $id): Category
    {
        return Category::findOrFail($id);
    }
}
