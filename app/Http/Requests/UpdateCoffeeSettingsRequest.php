<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoffeeSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['sometimes', 'boolean'],
            'frequency_weeks' => ['required', 'integer', 'between:1,8'],
            'matching_day' => ['required', 'integer', 'between:0,6'],
            'meeting_time' => ['required', 'date_format:H:i'],
            'meeting_duration' => ['required', 'integer', 'between:15,120'],
            'work_time_from' => ['required', 'date_format:H:i'],
            'work_time_to' => ['required', 'date_format:H:i', 'after:work_time_from'],
            'exclusion_weeks' => ['required', 'integer', 'between:0,26'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsData(): array
    {
        return array_merge($this->validated(), [
            'enabled' => $this->boolean('enabled'),
        ]);
    }
}