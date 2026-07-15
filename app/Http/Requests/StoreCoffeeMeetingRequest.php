<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StoreCoffeeMeetingRequest extends FormRequest
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
            'user_one_id' => ['required', 'integer', $this->activeMember()],
            'user_two_id' => ['required', 'integer', 'different:user_one_id', $this->activeMember()],
            'scheduled_at' => ['bail', 'required', 'date', $this->inTheFuture()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_two_id.different' => 'Participants must be two different employees.',
            'user_one_id.exists' => 'Participant must be an active employee.',
            'user_two_id.exists' => 'Participant must be an active employee.',
        ];
    }

    /**
     * Participants are looked up in members, not users: accounts without an
     * employee profile (e.g. the technical admin) cannot take part, and
     * deactivated employees are excluded just like in the automatic matching.
     */
    private function activeMember(): Exists
    {
        return Rule::exists('members', 'user_id')->where(
            fn ($query) => $query->whereIn(
                'user_id',
                DB::table('users')->select('id')->where('active', true),
            ),
        );
    }

    /**
     * A meeting in the past would be pointless: the bot would announce a call
     * that is already over, and the confirmation question - sent the morning
     * after the meeting day - would never be asked.
     */
    private function inTheFuture(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            // The form sends local time; validating against the framework
            // timezone (UTC) would shift it by the coffee timezone offset.
            $scheduledAt = CarbonImmutable::parse((string) $value, (string) config('coffee.timezone', 'UTC'));

            if ($scheduledAt->isPast()) {
                $fail('The meeting must be scheduled in the future.');
            }
        };
    }
}