<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $start_day;
    public $end_day;
    public $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($start_day, $end_day, $status)
    {
        $this->start_day = $start_day;
        $this->end_day   = $end_day;
        $this->status    = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Departments: Your Day-off/Vacation')
            ->markdown('emails.offtime.response');
    }
}
