<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResource',
    title: 'UserResource'
)]
class UserResource extends JsonResource
{
    #[OA\Property(property: 'id', description: 'id юзера', type: 'integer')]
    #[OA\Property(property: 'name', type: 'string')]
    #[OA\Property(property: 'email', type: 'string')]
    #[OA\Property(property: 'birthday', description: 'Дата рождения юзера', type: 'string')]
    #[OA\Property(property: 'phone', description: 'Телефон юзера', type: 'string')]
    #[OA\Property(property: 'is_mother', description: 'Родила ли уже юзер', type: 'boolean', example: 0)]
    #[OA\Property(property: 'pregnancy_start', description: 'Дата начала беременности', type: 'string', example: '2022-03-17')]
    #[OA\Property(property: 'baby_born', description: 'Дата рождения ребенка у юзера', type: 'string', example: '2022-12-17')]
    #[OA\Property(property: 'photo', description: 'Ссылка на фото юзера', type: 'string')]
    #[OA\Property(property: 'free_lecture_watched', description: 'Дата, когда последний раз пользователь смотрел бесплатную лекцию', type: 'datetime')]
    #[OA\Property(property: 'watched_lectures_count', description: 'Количество просмотренных лекций', type: 'integer')]
    #[OA\Property(property: 'saved_lectures_count', description: 'Количество сохраненных лекций', type: 'integer')]
    #[OA\Property(property: 'purchased_lectures_count', description: 'Количество купленных лекций', type: 'integer')]

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'phone' => $this->phone,
            'is_mother' => $this->is_mother,
            'pregnancy_start' => $this->pregnancy_start,
            'baby_born' => $this->baby_born,
            'photo' => $this->photo,
            'free_lecture_watched' => $this->free_lecture_watched,
            'watched_lectures_count' => $this->watchedLectures->count(),
            'saved_lectures_count' => $this->savedLectures->count(),
            'purchased_lectures_count' => $this->purchasedLectures->count()
        ];
    }
}
