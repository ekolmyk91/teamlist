<?php

namespace App\Services;

use App\Mail\offTime\MemberNewOffTimeMail;
use App\Mail\offTime\AdminEditOffTimeMail;
use App\Mail\offTime\AdminNewOffTimeMail;
use App\Member;
use Illuminate\Support\Facades\Mail;

class MailService {
    static function sendNewOffTimeRequest($start_day, $end_day, $full_name, $type, $link){
        Mail::to(explode(',', env('REQUEST_EMAILS')))
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

    static function sendAdminNewOffTimeMail($user_id, $start_day, $end_day, $status){
        Mail::to(Member::find($user_id)->email)
            ->send(
                new AdminNewOffTimeMail(
                    $start_day,
                    $end_day,
                    $status
                )
            );
    }

    static function sendAdminUpdateOffTimeMail($user_id, $start_day, $end_day, $status){
        Mail::to(Member::find($user_id)->email)
            ->send(
                new AdminEditOffTimeMail(
                    $start_day,
                    $end_day,
                    $status
                )
            );
    }
}
