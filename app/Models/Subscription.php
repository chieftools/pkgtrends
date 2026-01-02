<?php

namespace ChiefTools\Pkgtrends\Models;

use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Stayallive\Laravel\Eloquent\UUID\UsesUUID;
use Illuminate\Database\Eloquent\Casts\Attribute;
use ChiefTools\Pkgtrends\Mail\ConfirmSubscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string                              $id
 * @property string                              $email
 * @property string                              $report_id
 * @property \Carbon\Carbon|null                 $confirmed_at
 * @property \Carbon\Carbon|null                 $last_notified_at
 * @property bool                                $is_confirmed
 * @property \Carbon\Carbon                      $created_at
 * @property \Carbon\Carbon                      $updated_at
 * @property \ChiefTools\Pkgtrends\Models\Report $report
 */
class Subscription extends Model
{
    use UsesUUID;

    protected $fillable = [
        'email',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function (self $subscription) {
            Mail::to($subscription)->send(new ConfirmSubscription($subscription));
        });
    }

    public function scopeConfirmed(Builder $query): void
    {
        $query->whereNotNull('confirmed_at');
    }

    public function scopeNotNotifiedInLastDays(Builder $query, int $days = 6): void
    {
        $query->where(function (Builder $query) use ($days) {
            $query->whereNull('last_notified_at')
                ->orWhere('last_notified_at', '<', now()->subDays($days));
        });
    }

    public function scopeHasNotConfirmedInHours(Builder $query, int $hours = 48): void
    {
        $query->where(function (Builder $query) use ($hours) {
            $query->whereNull('confirmed_at')
                ->where('created_at', '<', now()->subHours($hours));
        });
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\ChiefTools\Pkgtrends\Models\Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function confirm(): void
    {
        if (!$this->is_confirmed) {
            $this->confirmed_at = now();
            $this->save();
        }
    }

    public function markNotified(): void
    {
        $this->last_notified_at = now();
        $this->save();
    }

    public function isConfirmed(): Attribute
    {
        return new Attribute(
            get: fn () => $this->confirmed_at !== null,
        );
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, \ChiefTools\Pkgtrends\Models\Subscription> */
    public static function findByEmail(string $email): Collection
    {
        return self::query()->where('email', '=', strtolower(trim($email)))->get();
    }

    public static function findOrCreate(string $email, Report $report): self
    {
        return $report->subscriptions()->firstOrCreate(['email' => strtolower(trim($email))]);
    }
}
