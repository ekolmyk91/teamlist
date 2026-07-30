<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

use App\CoffeeMeeting;
use App\CoffeeMeetingAnswer;
use App\CoffeeSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * The short feedback survey a participant fills in right after confirming that
 * the meeting took place. Attendance itself is already stored by then, so an
 * abandoned survey costs nothing but the answers.
 *
 * Questions come from config/coffee.php; every answer keeps a snapshot of the
 * question text, so the admin always reads the wording the employee saw.
 */
final class FeedbackSurvey
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_CHOICE = 'choice';
    public const TYPE_TEXT = 'text';

    private const TEXT_MAX_LENGTH = 2000;

    /** @var array<int, array<string, mixed>>|null */
    private ?array $questions = null;

    /**
     * Normalised question list: bool questions get their yes/no options and the
     * meeting length is substituted into the labels.
     *
     * @return array<int, array{key: string, type: string, label: string, required: bool, options: array<string, string>, max_length: int}>
     */
    public function questions(): array
    {
        return $this->questions ??= $this->buildQuestions();
    }

    public function isEmpty(): bool
    {
        return $this->questions() === [];
    }

    public function signedFormUrl(CoffeeMeeting $meeting, int $userId): string
    {
        return URL::temporarySignedRoute(
            'coffee.survey',
            now()->addDays((int) config('coffee.link_ttl_days', 7)),
            ['meeting' => $meeting->id, 'user' => $userId],
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = ['answers' => ['nullable', 'array']];

        foreach ($this->questions() as $question) {
            $field = 'answers.' . $question['key'];
            $presence = $question['required'] ? 'required' : 'nullable';

            $rules[$field] = $question['type'] === self::TYPE_TEXT
                ? [$presence, 'string', 'max:' . self::TEXT_MAX_LENGTH]
                : [$presence, 'string', 'in:' . implode(',', array_keys($question['options']))];
        }

        return $rules;
    }

    /**
     * The page is in Ukrainian and each message is rendered next to its own
     * question, so a short generic wording is enough.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->questions() as $question) {
            $field = 'answers.' . $question['key'];

            $messages[$field . '.required'] = 'Будь ласка, дайте відповідь на це запитання.';
            $messages[$field . '.in'] = 'Виберіть один із варіантів.';
            $messages[$field . '.string'] = 'Некоректна відповідь.';
            $messages[$field . '.max'] = 'Занадто довгий текст — максимум ' . self::TEXT_MAX_LENGTH . ' символів.';
        }

        return $messages;
    }

    /**
     * Stores one participant's answers. Idempotent: re-submitting the form
     * while the link is alive updates the previous answers.
     *
     * Attendance is re-read under a row lock rather than trusted from the
     * caller: the "it did not happen" link stays live in the same chat for a
     * week, and a click racing this submit would otherwise wipe the feedback a
     * moment before it is written, leaving answers on a meeting its own author
     * says never happened.
     *
     * @param array<string, mixed> $input answers keyed by question key
     *
     * @return bool false when the participant no longer confirms the meeting
     */
    public function store(CoffeeMeeting $meeting, int $userId, array $input): bool
    {
        return DB::transaction(function () use ($meeting, $userId, $input) {
            $locked = CoffeeMeeting::query()->lockForUpdate()->find($meeting->id);

            if ($locked === null || $locked->attendanceOf($userId) !== true) {
                return false;
            }

            foreach ($this->questions() as $question) {
                $raw = $input[$question['key']] ?? null;
                $value = is_scalar($raw) ? trim((string) $raw) : '';

                $identity = [
                    'meeting_id' => $meeting->id,
                    'user_id' => $userId,
                    'question_key' => $question['key'],
                ];

                // An optional question left blank stores nothing - and clears a
                // previous answer when the participant re-submits the form.
                if ($value === '') {
                    CoffeeMeetingAnswer::query()->where($identity)->delete();

                    continue;
                }

                CoffeeMeetingAnswer::query()->updateOrCreate($identity, [
                    'question_label' => $question['label'],
                    'answer_value' => $question['type'] === self::TYPE_TEXT ? null : $value,
                    'answer_text' => $question['type'] === self::TYPE_TEXT ? $value : null,
                ]);
            }

            return true;
        });
    }

    /**
     * Drops one participant's feedback. Used when they change their mind and
     * say the meeting did not happen after all: keeping a filled-in survey
     * next to "it never took place" would just confuse the admin.
     */
    public function forget(CoffeeMeeting $meeting, int $userId): void
    {
        CoffeeMeetingAnswer::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Previous answers keyed by question key, for pre-filling the form.
     *
     * @return array<string, string>
     */
    public function existingAnswers(CoffeeMeeting $meeting, int $userId): array
    {
        return CoffeeMeetingAnswer::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $userId)
            ->get()
            ->mapWithKeys(fn (CoffeeMeetingAnswer $answer) => [
                (string) $answer->question_key => (string) ($answer->answer_value ?? $answer->answer_text),
            ])
            ->all();
    }

    /**
     * Human label of a stored closed answer ("yes" -> "Так"), falling back to
     * the raw value when the question or option is no longer configured.
     */
    public function optionLabel(string $questionKey, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        foreach ($this->questions() as $question) {
            if ($question['key'] === $questionKey) {
                return $question['options'][$value] ?? $value;
            }
        }

        return $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildQuestions(): array
    {
        $duration = (int) CoffeeSetting::current()->meeting_duration;
        $types = [self::TYPE_BOOL, self::TYPE_CHOICE, self::TYPE_TEXT];
        $questions = [];

        foreach ((array) config('coffee.survey.questions', []) as $question) {
            $key = (string) ($question['key'] ?? '');
            $type = (string) ($question['type'] ?? '');

            if ($key === '' || ! in_array($type, $types, true)) {
                continue;
            }

            $options = $type === self::TYPE_BOOL
                ? ['yes' => 'Так', 'no' => 'Ні']
                : array_map('strval', (array) ($question['options'] ?? []));

            if ($type === self::TYPE_CHOICE && $options === []) {
                continue;
            }

            $questions[] = [
                'key' => $key,
                'type' => $type,
                'label' => str_replace(':duration', (string) $duration, (string) ($question['label'] ?? $key)),
                'required' => (bool) ($question['required'] ?? false),
                'options' => $options,
                'max_length' => self::TEXT_MAX_LENGTH,
            ];
        }

        return $questions;
    }
}
