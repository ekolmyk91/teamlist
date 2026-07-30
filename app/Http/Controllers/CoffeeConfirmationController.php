<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\CoffeeMeeting;
use App\Services\RandomCoffee\FeedbackSurvey;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class CoffeeConfirmationController extends Controller
{
    /**
     * Public signed endpoint hit from the Telegram bot's yes/no links.
     *
     * The attendance answer is stored right here, before anything else is
     * shown: whether the participant then fills in the feedback survey, skips
     * it or closes the page, the meeting stays marked as held.
     */
    public function __invoke(CoffeeMeeting $meeting, User $user, string $answer, FeedbackSurvey $survey): View
    {
        abort_unless(in_array($answer, ['yes', 'no'], true), 404);
        abort_unless($meeting->hasParticipant((int) $user->id), 403);

        // One transaction over both steps, on the row lock applyAnswer() takes:
        // a survey submit racing this click must not slip its answers into the
        // gap between flipping the answer and dropping the feedback.
        DB::transaction(function () use ($meeting, $user, $answer, $survey) {
            $meeting->applyAnswer((int) $user->id, $answer === 'yes');

            // Both links stay live for a week, so somebody who answered "yes"
            // and filled the survey in may still click "no" afterwards; their
            // feedback goes with the answer it belonged to.
            if ($answer === 'no') {
                $survey->forget($meeting, (int) $user->id);
            }
        });

        // Only somebody who says the meeting happened is asked for feedback.
        $askFeedback = $answer === 'yes' && ! $survey->isEmpty();

        return view('coffee.confirmation', [
            'attended' => $answer === 'yes',
            'submitted' => false,
            'questions' => $askFeedback ? $survey->questions() : [],
            'answers' => $askFeedback ? $survey->existingAnswers($meeting, (int) $user->id) : [],
            'formAction' => $askFeedback ? $survey->signedFormUrl($meeting, (int) $user->id) : null,
        ]);
    }
}
