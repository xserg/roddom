<?php

namespace App\Http\Resources;

use App\Models\AppInfo;
use App\Models\Threads\Participant;
use App\Traits\MoneyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[OA\Schema(
    schema: 'UserResource',
    title: 'UserResource'
)]
/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    use MoneyConversion;

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
    #[OA\Property(property: 'created_at', description: 'Когда создан аккаунт', type: 'string')]
    #[OA\Property(property: 'updated_at', description: 'Когда обновлен аккаунт', type: 'string')]
    public function toArray(Request $request): array
    {
        $refsCount =
            $this->referrals()->count() +
            $this->referralsSecondLevel()->count() +
            $this->referralsThirdLevel()->count() +
            $this->referralsFourthLevel()->count() +
            $this->referralsFifthLevel()->count();

        $inviteText = $this->resolveInviteQr();
        $inviteQrCode = QrCode::encoding('UTF-8')->size(250)->generate($inviteText)->toHtml();
        $inviteQrCodePng = base64_encode(QrCode::encoding('UTF-8')->format('png')->size(250)->generate($inviteText)->toHtml());

        return [
            'id' => $this->id,
            'polis' => $this->polis,
            'name' => $this->name,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'phone' => $this->phone,
            'is_mother' => $this->is_mother,
            'pregnancy_start' => $this->pregnancy_start,
            'baby_born' => $this->baby_born,
            'photo' => $this->photo ? env('APP_URL') . '/storage/' . $this->photo : '',
            'photo_small' => $this->photo_small ? env('APP_URL'). '/' . $this->photo_small : '',
            'next_free_lecture_available' => $this->next_free_lecture_available,
            'ref' => [
                'points_available' => self::coinsToRoubles($this->refPoints?->points ?? 0),
                'token' => $this->ref_token,
                'ref_link_qr' => $inviteQrCode,
                'ref_link_qr_png' => $inviteQrCodePng,
                'referer_id' => $this->referrer_id,
                'referrals_count' => $this->when($refsCount, $refsCount, 0),
            ],
            'watched_lectures_count' => $this->whenCounted('watchedLectures', $this->watched_lectures_count, 0),
            'list_watched_lectures_count' => $this->whenCounted('listWatchedLectures', $this->list_watched_lectures_count, 0),
            'saved_lectures_count' => $this->whenCounted('savedLectures', $this->saved_lectures_count, 0),
            'purchased_lectures_count' => $this->whenAppended('purchasedLecturesCounter', $this->purchased_lectures_counter, 0),
            'is_notification_read' => $this->is_notification_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->profile_fulfilled_at,
        ];
    }

    private function resolveInviteQr(): string
    {
        return
//            str_replace('x',
//                $this->name ?? $this->email,
//                AppInfo::first()?->user_invites_you_to_join ?? 'x приглашает Вас присоединиться к интересным материалам Школы Мам и Пап!') .
            config('app.frontend_url') . '/register?ref=' . $this->ref_token;
    }
}
