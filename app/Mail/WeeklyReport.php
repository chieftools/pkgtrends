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

    public string $title;

    public string $permalink;

    public Collection $dependencies;

    public Subscription $subscription;

    public function __construct(string $title, string $permalink, Collection $dependencies, Subscription $subscription)
    {
        $this->title        = $title;
        $this->permalink    = $permalink;
        $this->dependencies = $dependencies;
        $this->subscription = $subscription;
    }

    public function build(): self
    {
        return $this->markdown('emails.weekly', [
            'title'        => $this->title,
            'permalink'    => $this->permalink,
            'deps'         => $this->dependencies,
            'subscription' => $this->subscription,
        ])->subject("Weekly trends update: {$this->title}");
    }
}
