<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_fulfilled_at' => 'datetime',
    ];

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

    protected function purchasedLecturesCounter(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value,
            set: fn ($value) => $value
        );
    }

    public function canAccessFilament(): bool
    {
        return $this->is_admin;
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isProfileFulfilled(): bool
    {
        return (bool) $this->profile_fulfilled_at;
    }

    public function setProfileFulfilled(): void
    {
        $this->profile_fulfilled_at = now();
    }
}
