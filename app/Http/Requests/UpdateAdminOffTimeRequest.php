<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateAdminOffTimeRequest extends FormRequest
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
                'date',
                Rule::unique('off_time')
                    ->ignore($this->user_id, 'user_id')
                    ->where('user_id', $this->user_id),
            ),
            'end_day'  => array(
                'required',
                'date',
                Rule::unique('off_time')
                    ->ignore($this->user_id, 'user_id')
                    ->where('user_id', $this->user_id),
            ),
            'type_id'      => 'required|exists:off_time_types,id',
            'status'    => 'required|in:'. implode(',', config('constants.off_time_status'))
        ];
    }

    public function messages()
    {
        return [
            'start_day.unique' => 'Start Day already taken.',
            'end_day.unique'   => 'End Date already taken.',
        ];
    }
}
