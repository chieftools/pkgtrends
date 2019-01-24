<?php

namespace IronGate\Pkgtrends\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use IronGate\Pkgtrends\Models\Subscription;

class WeeklyReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $title;

    public $dependencies;

    public $subscription;

    public function __construct(string $title, Collection $dependencies, Subscription $subscription)
    {
        $this->title        = $title;
        $this->dependencies = $dependencies;
        $this->subscription = $subscription;
    }

    public function build(): self
    {
        return $this->markdown('emails.weekly', [
            'title'        => $this->title,
            'deps'         => $this->dependencies,
            'subscription' => $this->subscription,
        ])->subject("Weekly trends update: {$this->title}");
    }
}
