<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Filament\Widgets\Widget;

class CategoryPrices extends Widget
{
    public ?Category $record = null;

    protected static string $view = 'filament.resources.category-resource.widgets.category-price';

    protected function getViewData(): array
    {
        $form = $this->record->isSub() ?
            app(CategoryRepository::class)->formSubCategoryPrices($this->record) :
            app(CategoryRepository::class)->formMainCategoryPrices($this->record);

        return [
            'form' => $form
        ];
    }
}
