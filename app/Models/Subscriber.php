<?php

namespace IronGate\Pkgtrends\Models;

use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use IronGate\Pkgtrends\Models\Traits\UsesUUID;
use IronGate\Pkgtrends\Mail\ConfirmSubscription;

class Subscriber extends Model
{
    use UsesUUID;

    public $incrementing = false;

    protected $fillable = [
        'email',
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function (self $subscriber) {
            Mail::to($subscriber)->send(new ConfirmSubscription($subscriber));
        });
    }

    public function scopeConfirmed(Builder $query)
    {
        $query->whereNotNull('confirmed_at');
    }

    public function scopeNotNotifiedInLastDays(Builder $query, int $days = 6)
    {
        $query->where(function (Builder $query) use ($days) {
            $query->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', now()->subDays($days));
        });
    }

    public function scopeHasNotConfirmedInHours(Builder $query, int $hours = 48)
    {
        $query->where(function (Builder $query) use ($hours) {
            $query->whereNull('confirmed_at')
                  ->where('created_at', '<', now()->subHours($hours));
        });
    }

    public function confirm()
    {
        if (!$this->is_confirmed) {
            $this->confirmed_at = now();
            $this->save();
        }
    }

    public function wasNotified()
    {
        $this->last_notified_at = now();
        $this->save();
    }

    public function getIsConfirmedAttribute()
    {
        return $this->confirmed_at !== null;
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public static function findOrCreate(string $email, Report $report)
    {
        $email = strtolower(trim($email));

        $subscriber = self::query()->where('email', '=', $email)->where('report_id', '=', $report->id)->first();

        if ($subscriber === null) {
            $subscriber = $report->subscribers()->save(new self(compact('email')));
        }

        return $subscriber;
    }

    public static function findByEmail(string $email): Collection
    {
        return self::query()->where('email', '=', $email)->get();
    }
}
