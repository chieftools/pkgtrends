<?php

namespace IronGate\Pkgtrends\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use IronGate\Pkgtrends\Models\Subscription;

class ConfirmSubscription extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function build(): self
    {
        $packages = $this->subscription->report->getTrends()->getFormattedTitle();

        return $this->markdown('emails.confirm', [
            'subscription' => $this->subscription,
            'packages'     => $packages,
        ])->subject('Confirm your weekly Package Trends subscription!');
    }
}
