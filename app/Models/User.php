<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRelationships, HasTableAlias;

    protected $appends = ['purchased_lectures_counter'];

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
        'can_get_referrals_bonus'
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
        'can_get_referrals_bonus' => 'bool'
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (is_null($user->ref_token)) {
                $user->ref_token = Str::uuid();
            }
        });
    }

    public function watchedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_watched_lectures',
            'user_id',
            'lecture_id'
        )
            ->withPivot(['available_until'])
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    public function savedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_saved_lectures',
            'user_id',
            'lecture_id'
        )
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    public function listWatchedLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'user_to_list_watched_lectures',
            'user_id',
            'lecture_id'
        )
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function lectureSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', Lecture::class);
    }

    public function categorySubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', Category::class);
    }

    public function everythingPackSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', EverythingPack::class);
    }

    public function actualEverythingPackSubscriptions(): HasMany
    {
        return $this->subscriptions()
            ->where('subscriptionable_type', '=', EverythingPack::class)
            ->where('end_date', '>', now())
            ->latest('id');
    }

    public function actualCategorySubscriptions(): HasMany
    {
        return $this->categorySubscriptions()
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
        )->withDefault();
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(
            User::class,
            'referrer_id',
            'id'
        );
    }

    public function referralsOfReferrals(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->referrals(),
            (new User())->setAlias('users-alias')->referrals()
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

    public function hasReferrer(): bool
    {
        return (bool) $this->referrer_id;
    }

    protected function purchasedLecturesCounter(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value,
            set: fn ($value) => $value
        );
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

    public function markCantGetReferralsBonus()
    {
        return $this->forceFill([
            'can_get_referrals_bonus' => false,
        ])->save();
    }
}
