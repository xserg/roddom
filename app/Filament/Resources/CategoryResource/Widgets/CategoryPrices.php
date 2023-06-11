<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use App\Services\CategoryService;
use Filament\Widgets\Widget;

class CategoryPrices extends Widget
{
    public ?Category $record = null;

    protected static string $view = 'filament.resources.category-resource.widgets.category-price';

    protected function getViewData(): array
    {
        if ($this->record->isSub()) {
            $lecturesCount = $this->record->lectures()->count();
            $form = app(CategoryService::class)->formSubCategoryPrices($this->record);
        } else {
            $lecturesCount = $this->record->childrenCategories()->withCount('lectures')->get()->sum('lectures_count');
            $form = app(CategoryService::class)->formMainCategoryPrices($this->record);
        }

        return [
            'form' => $form,
            'lectures_count' => $lecturesCount
        ];
    }
}
