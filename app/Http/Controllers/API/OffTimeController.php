<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientOffTimeRequest;
use App\Mail\AdminNewOffTime;
use App\Member;
use App\OffTime;
use Illuminate\Support\Facades\Mail;

class OffTimeController extends Controller
{
    public function offTimeRequest(StoreClientOffTimeRequest $request)
    {

        $validated_data           = $request->validated();
        $validated_data['status'] = 'waiting_approve';

        try {
            $timeOffRequest = OffTime::create($validated_data);
        } catch (\Throwable $t) {

            return response()->json([
                'success' => false,
                'message' => "Problems with saving..., repeat please via Email",
            ], 400);
        }

        $member = Member::find($request->get('user_id'));

        try {
            Mail::to(Member::find($member->email))->send(new AdminNewOffTime($timeOffRequest, $member));
        } catch (\Throwable $t) {
            return response()->json([
                'success' => false,
                'message' => 'Request was created. But we have problem with email sending, repeat please via Email',
            ], 500);
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Request was sent.'
            ],
            201
        );
    }
}
