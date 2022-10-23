<?php

namespace App\Mail\offTime;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OffTimeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $start_day;
    protected $end_day;
    protected $status;
    protected $full_name;
    protected $type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($start_day, $end_day, $status, $full_name = null, $type = null)
    {
        $this->start_day = $start_day;
        $this->end_day   = $end_day;
        $this->status    = $status;
        $this->full_name = $full_name;
        $this->type      = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //
    }
}
