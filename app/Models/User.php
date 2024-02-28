<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RefTypeEnum;
use App\Models\Threads\Participant;
use App\Models\Threads\Thread;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRelationships, HasTableAlias;

    protected $fillable = [
        'email',
        'password',
        'name',
        'birthdate',
        'phone',
        'is_mother',
        'baby_born',
        'photo',
        'photo_small',
        'referrer_id',
        'ref_token',
        'can_get_referrals_bonus',
        'can_get_referrers_bonus',
        'next_free_lecture_available',
        'is_notification_read',
        'ref_type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_fulfilled_at' => 'datetime',
        'is_admin' => 'bool',
        'is_mother' => 'bool',
        'can_get_referrals_bonus' => 'bool',
        'can_get_referrers_bonus' => 'bool',
        'is_notification_read' => 'bool',
        'ref_type' => RefTypeEnum::class
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (is_null($user->ref_token)) {
                $user->ref_token = Str::uuid();
            }
        });
    }

    public function freeWatchedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_free_watched_lectures',
        )
            ->withPivot(['available_until'])
            ->withTimestamps();
    }

    public function watchedLecturesHistory(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, 'user_to_watched_lectures')
            ->using(WatchedLecturesUsersPivot::class)
            ->withTimestamps();
    }

    public function watchedLecturesHistoryToday(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, 'user_to_watched_lectures')
            ->using(WatchedLecturesUsersPivot::class)
            ->withTimestamps()
            ->wherePivotBetween('created_at', [today(), today()->addHours(24)]);
    }

    public function watchedLectures(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, 'user_to_watched_lectures')
            ->using(WatchedLecturesUsersPivot::class)
            ->distinct();
    }

    public function watchedLecturesToday(): BelongsToMany
    {
        return $this->belongsToMany(Lecture::class, 'user_to_watched_lectures')
            ->using(WatchedLecturesUsersPivot::class)
            ->wherePivotBetween('created_at', [today(), today()->addHours(24)])
            ->distinct();
    }

    public function savedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_saved_lectures',
        )
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    public function listWatchedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_list_watched_lectures',
        )
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->orderBy('created_at', 'desc');
    }

    public function actualSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)
            ->where('start_date', '<', now())
            ->where('end_date', '>', now())
            ->orderBy('created_at', 'desc');
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function lectureSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', Lecture::class);
    }

    public function categorySubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', Category::class);
    }

    public function everythingPackSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', EverythingPack::class);
    }

    public function actualEverythingPackSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', EverythingPack::class)
            ->where('start_date', '<', now())
            ->where('end_date', '>', now())
            ->latest('id');
    }

    public function actualCategorySubscriptions(): HasMany
    {
        return $this->categorySubscriptions()
            ->where('start_date', '<', now())
            ->where('end_date', '>', now());
    }

    public function promoSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', Promo::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function refPoints(): HasOne
    {
        return $this->hasOne(RefPoints::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'referrer_id'
        );
    }

    public function referrerSecondLevel(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->referrer(),
            (new User())->setAlias('users-alias-12')->referrer()
        );
    }

    public function referrerThirdLevel(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->referrerSecondLevel(),
            (new User())->setAlias('users-alias-13')->referrer()
        );
    }

    public function referrerFourthLevel(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->referrerThirdLevel(),
            (new User())->setAlias('users-alias-14')->referrer()
        );
    }

    public function referrerFifthLevel(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->referrerFourthLevel(),
            (new User())->setAlias('users-alias-15')->referrer()
        );
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(
            User::class,
            'referrer_id',
            'id'
        );
    }

    public function referralsSecondLevel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->referrals(),
            (new User())->setAlias('users-alias-2')->referrals()
        );
    }

    public function referralsThirdLevel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->referralsSecondLevel(),
            (new User())->setAlias('users-alias-3')->referrals()
        );
    }

    public function referralsFourthLevel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->referralsThirdLevel(),
            (new User())->setAlias('users-alias-4')->referrals()
        );
    }

    public function referralsFifthLevel(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->referralsFourthLevel(),
            (new User())->setAlias('users-alias-5')->referrals()
        );
    }

    public function refPointsGetPayments(): HasMany
    {
        return $this->hasMany(RefPointsPayments::class);
    }

    public function refPointsMadePayments(): HasMany
    {
        return $this->hasMany(RefPointsPayments::class, 'payer_id')
            ->where('user_id', null);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(\App\Models\Device::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function participantForThread(int $threadId): HasOne
    {
        return $this->hasOne(Participant::class)
            ->where('thread_id', $threadId);
    }

    public function scopeWithRefToken(Builder $query, ?string $refToken): void
    {
        $query->where('ref_token', $refToken);
    }

    public function hasReferrer(): bool
    {
        return $this->referrer()->exists();
    }

    public function hasNotReferrer(): bool
    {
        return $this->referrer()->doesntExist();
    }

    public function markNotificationRead()
    {
        return $this->fill([
            'is_notification_read' => true,
        ])->save();
    }

    public function markNotificationUnread()
    {
        return $this->fill([
            'is_notification_read' => false,
        ])->save();
    }

    public function canAccessFilament(): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isProfileFulfilled(): bool
    {
        return (bool) $this->profile_fulfilled_at;
    }

    public function setProfileFulfilled(): void
    {
        $this->profile_fulfilled_at = now();
    }

    public function canGetReferralsBonus()
    {
        return $this->can_get_referrals_bonus;
    }

    public function canGetReferrersBonus()
    {
        return $this->can_get_referrers_bonus;
    }

    public function markCantGetReferrersBonus(): bool
    {
        return $this->fill([
            'can_get_referrers_bonus' => false,
        ])->save();
    }

    public function markCantGetReferralsBonus(): bool
    {
        return $this->fill([
            'can_get_referrals_bonus' => false,
        ])->save();
    }

    public function markNextFreeLectureAvailable(int $hours = 24): bool
    {
        return $this->fill([
            'next_free_lecture_available' => now()->addHours($hours),
        ])->save();
    }

    public function determineTitleColumnName(): string
    {
        return ! is_null($this->name) ? 'name' : 'email';
    }

    public function purchasedLecturesCounter(): Attribute
    {
        $subs = $this->actualSubscriptions()->with('lectures')->get();

        $purchasedLecturesIds = $subs->map(fn ($subscription) => $subscription->lectures?->modelKeys())
            ->flatten()
            ->unique();

        return Attribute::make(
            get: fn () => count($purchasedLecturesIds),
        );
    }

    public function getName(): string
    {
        return $this->name ?? $this->email;
    }
}
