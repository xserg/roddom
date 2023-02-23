<?php

namespace App\Services;

use App\Models\LectureCategory;

class CategoryService
{
    public function isCategoryMain(LectureCategory $category): bool
    {
        return $category->parent_id == 0;
    }

    public function isCategorySub(LectureCategory $category): bool
    {
        return $category->parent_id != 0;
    }
}
