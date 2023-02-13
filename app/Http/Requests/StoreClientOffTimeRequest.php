<?php

namespace App\Http\Requests;

use App\Calendar;
use App\Member;
use App\OffTimeType;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientOffTimeRequest extends FormRequest
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
            'user_id'   => 'required|exists:members,user_id',
            'start_day' =>  array(
                'required',
                'date_format:Y-m-d',
                'after:today',
                Rule::unique('off_time')
                    ->where('user_id', $this->user_id),
            ),
            'end_day'  => array(
                'required',
                'date_format:Y-m-d',
                'after:today',
                Rule::unique('off_time')
                    ->where('user_id', $this->user_id),
            ),
            'type_id'   => 'required|exists:off_time_types,id',
        ];
    }

    /**
     * Additional validation rules
     *
     * @param $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ( $this->isRequestVacationForIntern($this->user_id, $this->type_id) ) {
                $validator->errors()->add('user', 'Intern can\'t take a vacation');
            }

            $holiday = $this->areDaysHolidays($this->start_day, $this->end_day );

            if ( !empty($holiday) ) {
                $validator->errors()->add('calendar', 'This day ' . $holiday .  ' is holiday. Change your choice please');
            }

        });
    }

    /**
     * Is request vacation and is user intern
     *
     * @param $user_id
     * @param $request_type
     * @return bool
     */
    protected function isRequestVacationForIntern($user_id, $request_type)
    {
        return (Member::isMemberTrainee($user_id) && OffTimeType::isVacation($request_type));
    }

    /**
     * Check and return holiday day
     *
     * @param $start_day
     * @param $end_day
     * @return false
     */
    protected function areDaysHolidays( $start_day, $end_day )
    {
        $start_day = Calendar::isHoliday($start_day) ? $start_day : false;
        $end_day   = Calendar::isHoliday($end_day) ? $end_day : false;

        return (!empty($start_day)) ? $start_day : $end_day;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(array( 'success' => false, 'message' => $validator->errors()), 400));
    }

    public function messages()
    {
        return [
            'start_day.unique' => 'Start Day already taken.',
            'end_day.unique'   => 'End Date already taken.',
        ];
    }
}
