<?php

namespace IronGate\Pkgtrends\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The trends data "nice" title.
     *
     * @var string
     */
    public $title;

    /**
     * The trends data to render.
     *
     * @var \Illuminate\Support\Collection
     */
    public $dependencies;

    /**
     * Create a new message instance.
     *
     * @param string $title
     * @param        $dependencies
     */
    public function __construct(string $title, Collection $dependencies)
    {
        $this->title        = $title;
        $this->dependencies = $dependencies;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->subject('Weekly Update: ' . $this->title)
                    ->markdown('emails.weekly', ['title' => $this->title, 'deps' => $this->dependencies]);
    }
}
