<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $full_name;
    public $start_day;
    public $end_day;
    public $type;
    public $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($full_name, $start_day, $end_day, $type, $link)
    {
        $this->full_name = $full_name;
        $this->start_day = $start_day;
        $this->end_day   = $end_day;
        $this->type      = $type;
        $this->link      = $link;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Departments: new Day-off/Vacation Request')
            ->markdown('emails.offtime.request');
    }
}
