<?php

declare(strict_types=1);

namespace Tests\Unit\Services\RandomCoffee;

use App\Services\RandomCoffee\FeedbackSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the question list that actually ships in config/coffee.php: it is
 * hand-edited, and a typo there would only surface in front of an employee.
 */
class FeedbackSurveyConfigTest extends TestCase
{
    use RefreshDatabase;

    public function testShippedQuestionsAreUsable(): void
    {
        $questions = (new FeedbackSurvey())->questions();

        $this->assertNotEmpty($questions, 'Every configured question was dropped as invalid.');

        $keys = array_column($questions, 'key');
        $this->assertSame(
            count($keys),
            count(array_unique($keys)),
            'Question keys must be unique - answers are stored per key.',
        );

        foreach ($questions as $question) {
            $this->assertNotSame('', trim($question['label']));
            // The meeting length must be resolved, never shown as ":duration".
            $this->assertStringNotContainsString(':duration', $question['label']);

            if ($question['type'] === FeedbackSurvey::TYPE_TEXT) {
                $this->assertSame([], $question['options']);

                continue;
            }

            $this->assertGreaterThanOrEqual(2, count($question['options']));
        }
    }

    public function testValidationRulesCoverEveryQuestion(): void
    {
        $survey = new FeedbackSurvey();
        $rules = $survey->rules();

        foreach ($survey->questions() as $question) {
            $this->assertArrayHasKey('answers.' . $question['key'], $rules);
        }
    }
}
