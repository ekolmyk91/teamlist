<?php

namespace App\Mail\offTime;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class MemberNewOffTimeMail extends OffTimeMail
{
    use Queueable, SerializesModels;

    protected $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    function __construct($start_day, $end_day, $full_name, $type, $link) {
        parent::__construct($start_day, $end_day, null, $full_name, $type);

        $this->link = $link;
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
            ->markdown('emails.offtime.member-new')
            ->with([
                'start_day' => $this->start_day,
                'end_day'   => $this->end_day,
                'full_name' => $this->full_name,
                'type'      => $this->type,
                'link'      => $this->link
            ]);
    }
}
