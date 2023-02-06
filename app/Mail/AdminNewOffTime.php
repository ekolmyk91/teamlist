<?php

namespace App\Mail;

use App\Member;
use App\OffTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminNewOffTime extends Mailable
{
    use Queueable, SerializesModels;

    protected $offTime;
    protected $member;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OffTime $offTime, Member $member)
    {
        $this->offTime = $offTime;
        $this->member = $member;
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
            ->markdown('emails.admin.new-off-time')
            ->with([
                'start_day' => $this->offTime->start_day,
                'end_day'   => $this->offTime->end_day,
                'type'      => $this->offTime->type,
                'link'      => config('app.url') . '/admin/off_time/' . $this->offTime->id . '/edit',
                'full_name' => $this->full_name,
            ]);
    }
}
