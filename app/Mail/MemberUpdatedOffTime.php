<?php

namespace App\Mail;

use App\OffTime;
use App\OffTimeType;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MemberUpdatedOffTime extends Mailable
{
    use Queueable, SerializesModels;

    protected $offTime;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OffTime $offTime)
    {
        $this->offTime = $offTime;
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
            ->markdown('emails.member.updated-off-time')
            ->with([
                'start_day' => $this->offTime->start_day,
                'end_day'   => $this->offTime->end_day,
                'status'    => $this->offTime->status,
                'type'      => OffTimeType::find($this->offTime->type_id)->name,
            ]);
    }
}
