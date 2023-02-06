<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminOffTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'user_id'   => 'required|exists:members,user_id|unique:off_time,user_id,NULL,NULL,start_day,'.$request['start_day'].'|unique:off_time,user_id,NULL,NULL,end_day,'.$request['end_day'],
            'start_day' => 'required|date|unique:off_time,start_day,NULL,NULL,user_id,'.$request['user_id'],
            'end_day'   => 'required|date|unique:off_time,end_day,NULL,NULL,user_id,'.$request['user_id'],
            'type_id'   => 'required|exists:off_time_types,id',
            'status'    => 'required|in:'. implode(',', config('constants.off_time_status'))
        ];
    }
}
