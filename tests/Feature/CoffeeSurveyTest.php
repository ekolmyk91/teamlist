<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\CoffeeMeeting;
use App\CoffeeMeetingAnswer;
use App\Member;
use App\Services\RandomCoffee\FeedbackSurvey;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CoffeeSurveyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A fixed question set: the shipped one may be re-worded any time.
        config(['coffee.survey.questions' => [
            [
                'key' => 'useful',
                'type' => 'bool',
                'label' => 'Чи була зустріч корисною?',
                'required' => true,
            ],
            [
                'key' => 'again',
                'type' => 'choice',
                'label' => 'Ще раз?',
                'options' => ['yes' => 'Так', 'no' => 'Ні', 'maybe' => 'Можливо'],
                'required' => true,
            ],
            [
                'key' => 'duration_enough',
                'type' => 'bool',
                'label' => 'Чи достатньо :duration хвилин?',
                'required' => true,
            ],
            [
                'key' => 'suggestions',
                'type' => 'text',
                'label' => 'Побажання',
                'required' => false,
            ],
        ]]);
    }

    public function testConfirmingAttendanceShowsTheSurvey(): void
    {
        $meeting = $this->createMeeting();

        $this->get($this->confirmUrl($meeting, (int) $meeting->user_one_id, 'yes'))
            ->assertOk()
            ->assertSee('Чи була зустріч корисною?')
            // The meeting length comes from the settings, not from the wording.
            ->assertSee('Чи достатньо 30 хвилин?')
            ->assertSee('Можливо')
            ->assertSee(sprintf('/coffee/survey/%d/%d', $meeting->id, $meeting->user_one_id), false);
    }

    public function testAttendanceIsStoredBeforeTheSurveyIsEvenShown(): void
    {
        $meeting = $this->createMeeting();

        $this->get($this->confirmUrl($meeting, (int) $meeting->user_one_id, 'yes'))->assertOk();

        // Whether the participant fills the survey in, skips it or closes the
        // page, the meeting stays marked as held.
        $meeting->refresh();
        $this->assertTrue($meeting->user_one_attended);
        $this->assertSame(CoffeeMeeting::STATUS_HELD, $meeting->status);
        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testMeetingThatDidNotHappenIsNotAskedForFeedback(): void
    {
        $meeting = $this->createMeeting();

        $this->get($this->confirmUrl($meeting, (int) $meeting->user_one_id, 'no'))
            ->assertOk()
            ->assertSee('Дякуємо за відповідь!')
            ->assertDontSee('Побажання');
    }

    public function testAnswersAreStored(): void
    {
        $meeting = $this->confirmedMeeting();
        $userId = (int) $meeting->user_one_id;

        $this->post($this->surveyUrl($meeting, $userId), ['answers' => [
            'useful' => 'yes',
            'again' => 'maybe',
            'duration_enough' => 'no',
            'suggestions' => 'Більше часу на розмову',
        ]])->assertOk()->assertSee('Дякуємо за ваш фідбек!');

        $answers = CoffeeMeetingAnswer::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('question_key');

        $this->assertCount(4, $answers);
        $this->assertSame('yes', $answers['useful']->answer_value);
        $this->assertSame('maybe', $answers['again']->answer_value);
        $this->assertSame('no', $answers['duration_enough']->answer_value);

        // Free-form answers go to answer_text, closed ones keep their value.
        $this->assertNull($answers['suggestions']->answer_value);
        $this->assertSame('Більше часу на розмову', $answers['suggestions']->answer_text);

        // The wording the employee saw is snapshotted with the answer.
        $this->assertSame('Чи достатньо 30 хвилин?', $answers['duration_enough']->question_label);
    }

    public function testOptionalQuestionLeftBlankStoresNothing(): void
    {
        $meeting = $this->confirmedMeeting();

        $this->post($this->surveyUrl($meeting, (int) $meeting->user_one_id), ['answers' => [
            'useful' => 'no',
            'again' => 'no',
            'duration_enough' => 'yes',
            'suggestions' => '   ',
        ]])->assertOk();

        $this->assertSame(3, CoffeeMeetingAnswer::count());
        $this->assertSame(0, CoffeeMeetingAnswer::query()->where('question_key', 'suggestions')->count());
    }

    public function testMissingRequiredAnswerIsRejected(): void
    {
        $meeting = $this->confirmedMeeting();

        $this->post($this->surveyUrl($meeting, (int) $meeting->user_one_id), ['answers' => [
            'useful' => 'yes',
            'suggestions' => 'Все супер',
        ]])
            ->assertOk()
            ->assertSee('Будь ласка, дайте відповідь на це запитання.')
            // The form comes back with what was already filled in.
            ->assertSee('Ще раз?')
            ->assertSee('Все супер');

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testUnknownOptionIsRejected(): void
    {
        $meeting = $this->confirmedMeeting();

        $this->post($this->surveyUrl($meeting, (int) $meeting->user_one_id), ['answers' => [
            'useful' => 'yes',
            'again' => 'someday',
            'duration_enough' => 'yes',
        ]])
            ->assertOk()
            ->assertSee('Виберіть один із варіантів.');

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testValidationErrorDoesNotExtendTheLinkLifetime(): void
    {
        $meeting = $this->confirmedMeeting();
        $expiresAt = now()->addMinutes(30);

        $url = URL::temporarySignedRoute('coffee.survey', $expiresAt, [
            'meeting' => $meeting->id,
            'user' => $meeting->user_one_id,
        ]);

        // The form comes back pointing at the same signed URL. Re-signing it
        // here would hand out a fresh TTL for every invalid submit, and the
        // signature is the only authorisation this page has.
        $this->post($url, ['answers' => ['useful' => 'yes']])
            ->assertOk()
            ->assertSee('expires=' . $expiresAt->getTimestamp(), false);
    }

    public function testExpiredSurveyLinkIsRejected(): void
    {
        $meeting = $this->confirmedMeeting();

        $url = URL::temporarySignedRoute('coffee.survey', now()->subMinute(), [
            'meeting' => $meeting->id,
            'user' => $meeting->user_one_id,
        ]);

        $this->post($url, ['answers' => [
            'useful' => 'yes',
            'again' => 'yes',
            'duration_enough' => 'yes',
        ]])->assertForbidden();

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testResubmittingUpdatesTheAnswers(): void
    {
        $meeting = $this->confirmedMeeting();
        $userId = (int) $meeting->user_one_id;
        $url = $this->surveyUrl($meeting, $userId);

        $this->post($url, ['answers' => [
            'useful' => 'yes',
            'again' => 'yes',
            'duration_enough' => 'yes',
            'suggestions' => 'Перша думка',
        ]])->assertOk();

        $this->post($url, ['answers' => [
            'useful' => 'no',
            'again' => 'maybe',
            'duration_enough' => 'yes',
            'suggestions' => '',
        ]])->assertOk();

        $answers = CoffeeMeetingAnswer::query()->get()->keyBy('question_key');

        $this->assertCount(3, $answers);
        $this->assertSame('no', $answers['useful']->answer_value);
        $this->assertSame('maybe', $answers['again']->answer_value);
        // A cleared optional answer is removed rather than left stale.
        $this->assertFalse($answers->has('suggestions'));
    }

    public function testFormIsPrefilledWithPreviousAnswers(): void
    {
        $meeting = $this->confirmedMeeting();
        $userId = (int) $meeting->user_one_id;

        $this->post($this->surveyUrl($meeting, $userId), ['answers' => [
            'useful' => 'yes',
            'again' => 'yes',
            'duration_enough' => 'yes',
            'suggestions' => 'Хочу довші зустрічі',
        ]])->assertOk();

        $this->get($this->confirmUrl($meeting, $userId, 'yes'))
            ->assertOk()
            ->assertSee('Хочу довші зустрічі');
    }

    public function testUnsignedSubmissionIsRejected(): void
    {
        $meeting = $this->confirmedMeeting();

        $this->post(sprintf('/coffee/survey/%d/%d', $meeting->id, $meeting->user_one_id), [
            'answers' => ['useful' => 'yes', 'again' => 'yes', 'duration_enough' => 'yes'],
        ])->assertForbidden();

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testStrangerCannotSubmitFeedback(): void
    {
        $meeting = $this->confirmedMeeting();
        $stranger = $this->createEmployee('stranger@w4p.com');

        $this->post($this->surveyUrl($meeting, (int) $stranger->user_id), [
            'answers' => ['useful' => 'yes', 'again' => 'yes', 'duration_enough' => 'yes'],
        ])->assertForbidden();

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testFeedbackRequiresAConfirmedMeeting(): void
    {
        $meeting = $this->createMeeting();
        $userId = (int) $meeting->user_one_id;
        $payload = ['answers' => ['useful' => 'yes', 'again' => 'yes', 'duration_enough' => 'yes']];

        // Nobody confirmed anything yet.
        $this->post($this->surveyUrl($meeting, $userId), $payload)->assertForbidden();

        // ... and "it did not happen" carries no feedback either.
        $meeting->applyAnswer($userId, false);
        $this->post($this->surveyUrl($meeting, $userId), $payload)->assertForbidden();

        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testChangingTheAnswerToNotHeldRemovesTheFeedback(): void
    {
        $meeting = $this->confirmedMeeting();
        $userId = (int) $meeting->user_one_id;

        $this->post($this->surveyUrl($meeting, $userId), ['answers' => [
            'useful' => 'yes',
            'again' => 'yes',
            'duration_enough' => 'yes',
            'suggestions' => 'Було чудово',
        ]])->assertOk();

        // Both links stay live for a week: clicking "no" afterwards must not
        // leave feedback hanging on a meeting its author says never happened.
        $this->get($this->confirmUrl($meeting, $userId, 'no'))->assertOk();

        $this->assertSame(0, CoffeeMeetingAnswer::count());
        $this->assertFalse($meeting->fresh()->user_one_attended);
    }

    public function testOnlyTheSwitchingParticipantLosesTheirFeedback(): void
    {
        $meeting = $this->confirmedMeeting();
        $one = (int) $meeting->user_one_id;
        $two = (int) $meeting->user_two_id;
        $meeting->applyAnswer($two, true);

        foreach ([$one, $two] as $userId) {
            $this->post($this->surveyUrl($meeting, $userId), ['answers' => [
                'useful' => 'yes',
                'again' => 'yes',
                'duration_enough' => 'yes',
            ]])->assertOk();
        }

        $this->get($this->confirmUrl($meeting, $one, 'no'))->assertOk();

        $this->assertSame(0, CoffeeMeetingAnswer::query()->where('user_id', $one)->count());
        $this->assertSame(3, CoffeeMeetingAnswer::query()->where('user_id', $two)->count());
    }

    public function testSubmitLosesToANotHeldClickThatLandsMidRequest(): void
    {
        $meeting = $this->confirmedMeeting();
        $userId = (int) $meeting->user_one_id;

        // The request has already read the meeting - attendance still true -
        // when the "it did not happen" click lands and flips the row behind its
        // back. Trusting that stale read would write feedback onto a meeting
        // whose owner has just said it never happened.
        $stale = CoffeeMeeting::query()->find($meeting->id);
        CoffeeMeeting::query()->whereKey($meeting->id)->update(['user_one_attended' => false]);

        $stored = app(FeedbackSurvey::class)->store($stale, $userId, [
            'useful' => 'yes',
            'again' => 'yes',
            'duration_enough' => 'yes',
        ]);

        $this->assertTrue($stale->user_one_attended, 'the caller still holds the stale value');
        $this->assertFalse($stored);
        $this->assertSame(0, CoffeeMeetingAnswer::count());
    }

    public function testSurveyIsSkippedWhenNoQuestionsAreConfigured(): void
    {
        config(['coffee.survey.questions' => []]);

        $meeting = $this->createMeeting();

        $this->get($this->confirmUrl($meeting, (int) $meeting->user_one_id, 'yes'))
            ->assertOk()
            ->assertSee('Дякуємо за відповідь!')
            ->assertDontSee('Надіслати');

        $this->post($this->surveyUrl($meeting, (int) $meeting->user_one_id), ['answers' => []])
            ->assertNotFound();
    }

    private function confirmedMeeting(): CoffeeMeeting
    {
        $meeting = $this->createMeeting();
        $meeting->applyAnswer((int) $meeting->user_one_id, true);

        return $meeting;
    }

    private function createMeeting(): CoffeeMeeting
    {
        $one = $this->createEmployee('one@w4p.com');
        $two = $this->createEmployee('two@w4p.com');

        return CoffeeMeeting::create([
            'user_one_id' => $one->user_id,
            'user_two_id' => $two->user_id,
            'scheduled_at' => '2026-07-14 12:00:00',
            'meeting_url' => 'https://meet.jit.si/w4p-coffee-test',
        ]);
    }

    private function createEmployee(string $email): Member
    {
        $user = User::create([
            'name' => $email,
            'email' => $email,
            'password' => 'secret-password',
            'active' => true,
        ]);

        return Member::create([
            'user_id' => $user->id,
            'name' => 'Name-' . $user->id,
            'surname' => 'Surname-' . $user->id,
            'email' => $email,
        ]);
    }

    private function confirmUrl(CoffeeMeeting $meeting, int $userId, string $answer): string
    {
        return URL::signedRoute('coffee.confirm', [
            'meeting' => $meeting->id,
            'user' => $userId,
            'answer' => $answer,
        ]);
    }

    private function surveyUrl(CoffeeMeeting $meeting, int $userId): string
    {
        return URL::temporarySignedRoute('coffee.survey', now()->addDay(), [
            'meeting' => $meeting->id,
            'user' => $userId,
        ]);
    }
}
