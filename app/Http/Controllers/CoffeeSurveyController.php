<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\CoffeeMeeting;
use App\Services\RandomCoffee\FeedbackSurvey;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;

class CoffeeSurveyController extends Controller
{
    /**
     * Public signed endpoint behind the "yes, it happened" answer: stores the
     * feedback survey.
     *
     * Deliberately session-free - the link may be opened days later from
     * Telegram's in-app browser - so the signature is the only authentication
     * and validation errors are rendered straight back into the same page
     * instead of redirecting through the session.
     */
    public function __invoke(Request $request, CoffeeMeeting $meeting, User $user, FeedbackSurvey $survey): View
    {
        abort_unless($meeting->hasParticipant((int) $user->id), 403);
        abort_if($survey->isEmpty(), 404);
        // Feedback only belongs to a meeting this participant confirmed as held;
        // attendance itself was stored when they clicked the link.
        abort_unless($meeting->attendanceOf((int) $user->id) === true, 403);

        $input = $this->answers($request);
        $validator = Validator::make(['answers' => $input], $survey->rules(), $survey->messages());

        if ($validator->fails()) {
            return view('coffee.confirmation', [
                'attended' => true,
                'submitted' => false,
                'questions' => $survey->questions(),
                'answers' => $input,
                // The very URL that was just signature-checked, not a freshly
                // minted one: re-signing here would let anyone holding the link
                // extend its lifetime for ever by posting invalid answers, and
                // the signature is the only authorisation this page has.
                'formAction' => $request->fullUrl(),
                'errors' => (new ViewErrorBag())->put('default', $validator->errors()),
            ]);
        }

        // Refused when the participant confirmed the meeting a moment ago but
        // has since clicked "it did not happen" - see FeedbackSurvey::store().
        if (! $survey->store($meeting, (int) $user->id, $input)) {
            return view('coffee.confirmation', [
                'attended' => false,
                'submitted' => false,
                'questions' => [],
                'answers' => [],
                'formAction' => null,
            ]);
        }

        return view('coffee.confirmation', [
            'attended' => true,
            'submitted' => true,
            'questions' => [],
            'answers' => [],
            'formAction' => null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function answers(Request $request): array
    {
        $answers = [];

        foreach ((array) $request->input('answers', []) as $key => $value) {
            $answers[(string) $key] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $answers;
    }
}
