<?php

namespace IronGate\Pkgtrends\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use IronGate\Pkgtrends\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmSubscription extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subscriber;

    /**
     * Create a new message instance.
     *
     * @param \IronGate\Pkgtrends\Models\Subscriber $subscriber
     */
    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $packages = $this->subscriber->report->getTrends()->getFormattedTitle();

        return $this->markdown('emails.confirm', [
            'subscription' => $this->subscriber,
            'packages'     => $packages,
        ])->subject('Confirm your weekly Package Trends subscription');
    }
}
