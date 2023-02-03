<?php

namespace App\Services;

use App\Mail\offTime\MemberNewOffTimeMail;
use App\Mail\offTime\AdminEditOffTimeMail;
use App\Mail\offTime\AdminNewOffTimeMail;
use App\Member;
use Illuminate\Support\Facades\Mail;

class MailService {
    static function sendNewOffTimeRequest($start_day, $end_day, $full_name, $type, $link){
        Mail::to(explode(',', config('mail.off_time_request_recipients')))
            ->send(
                new MemberNewOffTimeMail(
                    $start_day,
                    $end_day,
                    $full_name,
                    $type,
                    $link
                )
            );
    }

}
