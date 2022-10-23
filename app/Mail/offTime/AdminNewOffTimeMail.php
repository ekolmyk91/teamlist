<?php

namespace App\Mail\offTime;

class AdminNewOffTimeMail extends OffTimeMail
{

    function __construct($start_day, $end_day, $status) {
        parent::__construct($start_day, $end_day, $status);
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
            ->markdown('emails.offtime.admin-new')
            ->with([
                'start_day' => $this->start_day,
                'end_day'   => $this->end_day,
                'status'    => $this->status,
            ]);;
    }
}
