<?php

namespace App\Http\Controllers\API;

use App\Calendar;
use App\Http\Controllers\Controller;
use App\Mail\RequestMail;
use App\Member;
use App\OffTime;
use App\OffTimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OffTimeController extends Controller
{
    public function offTimeRequest(Request $request)
    {
        $errors = false;
        $validation = Validator::make($request->all(), [
            'user_id'   => 'required|exists:members,user_id|unique:off_time,user_id,NULL,NULL,start_day,'.$request['start_day'].'|unique:off_time,user_id,NULL,NULL,end_day,'.$request['end_day'],
            'start_day' => 'required|date_format:Y-m-d|after:today|unique:off_time,start_day,NULL,NULL,user_id,'.$request['user_id'],
            'end_day'   => 'required|date_format:Y-m-d|after:today|unique:off_time,end_day,NULL,NULL,user_id,'.$request['user_id'],
            'type_id'   => 'required|exists:off_time_types,id',
        ]);

        if ($validation->fails()) {
            $errors = true;
        }

        if ( Member::is_member_trainee($request->get('user_id')) && OffTimeType::is_request_vacation($request->get('type_id')) ) {
            $validation->errors()->add('user', 'Intern can\'t take a vacation');
            $errors = true;
        }

        $start_day = Calendar::is_holiday($request->get('start_day')) ? $request->get('start_day') : '';
        $end_day   = Calendar::is_holiday($request->get('end_day')) ? $request->get('end_day') : '';
        $holiday   = !empty($start_day) ? $start_day : $end_day;

        if (!empty($holiday)) {
            $validation->errors()->add('calendar', 'This day ' . $holiday .  ' is holiday. Change your choice please');
            $errors = true;
        }

        if (isset($errors)) {
            return response()->json([
                'success' => false,
                'message' => $validation->messages(),
            ], 400);
        }

        $data           = $request->all();
        $data['status'] = 'waiting_approve';

        try {
            $timeOffRequest = OffTime::create($data);
            $member         = Member::find($request->get('user_id'));
            $full_name      = $member->name . ' ' . $member->surname;
            $type           = OffTimeType::find($request->get('type_id'))->name;
            $link           = env('APP_URL') . '/admin/off_time/' . $timeOffRequest->id . '/edit';

            Mail::to(explode(',', env('REQUEST_EMAILS')))
                ->send(
                    new RequestMail(
                        $full_name,
                        $request->get('start_day'),
                        $request->get('end_day'),
                        $type,
                        $link
                    )
                );

        } catch (\Throwable $t) {

            return response()->json([
                'success' => false,
                'message' => "Problems with saving...",
            ], 400);
        }

        return response()->json(
            [
                'success' => true,
                'message' => "Request sent"
            ],
            201
        );
    }
}
