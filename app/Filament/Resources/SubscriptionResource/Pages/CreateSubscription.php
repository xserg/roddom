<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Promo;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $users = array_shift($data);
        if ($data['subscriptionable_type'] === Lecture::class) {
            $lecturesCount = 1;
        } elseif ($data['subscriptionable_type'] === Category::class) {
            $category = Category::findOrFail($data['subscriptionable_id']);
            $lectures = $category->isMain() ? $category->childrenCategoriesLectures() : $category->lectures();

            $lecturesCount = $lectures->count();
        } elseif ($data['subscriptionable_type'] === Promo::class) {
            $lecturesCount = Lecture::promo()->count();
        } elseif ($data['subscriptionable_type'] === EverythingPack::class) {
            $lecturesCount = Lecture::count();
        }

        foreach ($users as $user) {
            $last = $this->getModel()::create([
                'user_id' => $user,
                'description' => 'Создана в админке',
                'lectures_count' => $lecturesCount,
                ...$data,
            ]);
        }

        return $last;
    }
}
