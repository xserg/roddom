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
    #[OA\Property(property: 'birthdate', description: 'Дата рождения юзера', type: 'string')]
    #[OA\Property(property: 'phone', description: 'Телефон юзера', type: 'string')]
    #[OA\Property(property: 'is_mother', description: 'Родила ли уже юзер', type: 'boolean', example: 0)]
    #[OA\Property(property: 'pregnancy_start', description: 'Дата начала беременности', type: 'string', example: '2022-03-17')]
    #[OA\Property(property: 'baby_born', description: 'Дата рождения ребенка у юзера', type: 'string', example: '2022-12-17')]
    #[OA\Property(property: 'photo', description: 'Ссылка на 300x300 фото юзера', type: 'string')]
    #[OA\Property(property: 'photo_small', description: 'Ссылка на 150x150 фото юзера', type: 'string')]
    #[OA\Property(property: 'next_free_lecture_available', description: 'Дата, когда пользователь сможет смотреть бесплатную лекцию', type: 'datetime')]
    #[OA\Property(property: 'watched_lectures_count', description: 'Количество просмотренные лекции', type: 'integer')]
    #[OA\Property(property: 'saved_lectures_count', description: 'Количество сохраненных лекции', type: 'integer')]
    #[OA\Property(property: 'purchased_lectures_count', description: 'Количество купленных лекции', type: 'integer')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'phone' => $this->phone,
            'is_mother' => $this->is_mother,
            'pregnancy_start' => $this->pregnancy_start,
            'baby_born' => $this->baby_born,
            'photo' => $this->photo,
            'photo_small' => $this->photo_small,
            'next_free_lecture_available' => $this->next_free_lecture_available,
            'watched_lectures_count' =>
                $this->whenNotNull($this->watched_lectures_count, default: 0),
            'saved_lectures_count' =>
                $this->whenNotNull($this->saved_lectures_count, default: 0),
            'purchased_lectures_count' =>
                $this->whenNotNull($this->purchased_lectures_count, default: 0)
        ];
    }
}
