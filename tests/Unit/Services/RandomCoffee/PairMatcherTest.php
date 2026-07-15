<?php

declare(strict_types=1);

namespace Tests\Unit\Services\RandomCoffee;

use App\Services\RandomCoffee\PairMatcher;
use PHPUnit\Framework\TestCase;

class PairMatcherTest extends TestCase
{
    private PairMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new PairMatcher();
    }

    public function testEvenNumberOfParticipantsAllPaired(): void
    {
        $pairs = $this->matcher->match([1, 2, 3, 4, 5, 6], []);

        $this->assertCount(3, $pairs);
        $this->assertPairsCoverUsersOnce($pairs, 6);
    }

    public function testOddNumberOfParticipantsOneSkips(): void
    {
        $pairs = $this->matcher->match([1, 2, 3, 4, 5], []);

        $this->assertCount(2, $pairs);
        $used = $this->flatten($pairs);
        $this->assertCount(4, array_unique($used));
    }

    public function testFewerThanTwoParticipantsGivesNoPairs(): void
    {
        $this->assertSame([], $this->matcher->match([], []));
        $this->assertSame([], $this->matcher->match([7], []));
    }

    public function testRecentPairsAreAvoided(): void
    {
        $recent = [PairMatcher::pairKey(1, 2), PairMatcher::pairKey(3, 4)];

        for ($i = 0; $i < 25; $i++) {
            $pairs = $this->matcher->match([1, 2, 3, 4], $recent);

            $this->assertCount(2, $pairs);
            foreach ($pairs as [$a, $b]) {
                $this->assertNotContains(PairMatcher::pairKey($a, $b), $recent);
            }
        }
    }

    public function testFallsBackToRepeatWhenNoAlternativeExists(): void
    {
        // Every combination of 1..4 is recent — matching must still happen.
        $recent = [
            PairMatcher::pairKey(1, 2),
            PairMatcher::pairKey(1, 3),
            PairMatcher::pairKey(1, 4),
            PairMatcher::pairKey(2, 3),
            PairMatcher::pairKey(2, 4),
            PairMatcher::pairKey(3, 4),
        ];

        $pairs = $this->matcher->match([1, 2, 3, 4], $recent);

        $this->assertCount(2, $pairs);
        $this->assertPairsCoverUsersOnce($pairs, 4);
    }

    public function testBacktrackingFindsTheOnlyValidCombination(): void
    {
        // Only valid pairing is (1,3)+(2,4): every other combination is recent.
        $recent = [
            PairMatcher::pairKey(1, 2),
            PairMatcher::pairKey(1, 4),
            PairMatcher::pairKey(3, 4),
            PairMatcher::pairKey(2, 3),
        ];

        for ($i = 0; $i < 25; $i++) {
            $pairs = $this->matcher->match([1, 2, 3, 4], $recent);

            $keys = array_map(fn (array $pair) => PairMatcher::pairKey($pair[0], $pair[1]), $pairs);
            sort($keys);
            $this->assertSame(['1-3', '2-4'], $keys);
        }
    }

    public function testDuplicateIdsAreIgnored(): void
    {
        $pairs = $this->matcher->match([1, 1, 2, 2], []);

        $this->assertCount(1, $pairs);
        $this->assertPairsCoverUsersOnce($pairs, 2);
    }

    public function testUnsatisfiableLargeTeamFinishesQuicklyViaFallback(): void
    {
        // 40 participants where EVERY pair is recent: exhaustive backtracking
        // would explode combinatorially; the step budget must cut it off and
        // the greedy fallback must still pair everyone.
        $userIds = range(1, 40);
        $recent = [];
        foreach ($userIds as $a) {
            foreach ($userIds as $b) {
                if ($a < $b) {
                    $recent[] = PairMatcher::pairKey($a, $b);
                }
            }
        }

        $start = microtime(true);
        $pairs = $this->matcher->match($userIds, $recent);
        $elapsed = microtime(true) - $start;

        $this->assertCount(20, $pairs);
        $this->assertPairsCoverUsersOnce($pairs, 40);
        $this->assertLessThan(2.0, $elapsed, 'Matching must not hang on unsatisfiable graphs');
    }

    public function testNearUnsatisfiableGraphFinishesAndPairsEveryone(): void
    {
        // User 1 has met everyone (no valid partner), the rest are free:
        // a perfect repeat-free matching is impossible, but all 18 must
        // still be paired with at most one repeat.
        $userIds = range(1, 18);
        $recent = [];
        foreach (range(2, 18) as $other) {
            $recent[] = PairMatcher::pairKey(1, $other);
        }

        $start = microtime(true);
        $pairs = $this->matcher->match($userIds, $recent);
        $elapsed = microtime(true) - $start;

        $this->assertCount(9, $pairs);
        $this->assertPairsCoverUsersOnce($pairs, 18);
        $this->assertLessThan(2.0, $elapsed, 'Matching must not hang on near-unsatisfiable graphs');

        $repeats = 0;
        foreach ($pairs as [$a, $b]) {
            if (in_array(PairMatcher::pairKey($a, $b), $recent, true)) {
                $repeats++;
            }
        }
        $this->assertLessThanOrEqual(1, $repeats);
    }

    /**
     * @param array<int, array{0: int, 1: int}> $pairs
     */
    private function assertPairsCoverUsersOnce(array $pairs, int $expectedUsers): void
    {
        $used = $this->flatten($pairs);
        $this->assertCount($expectedUsers, $used);
        $this->assertCount($expectedUsers, array_unique($used));
    }

    /**
     * @param array<int, array{0: int, 1: int}> $pairs
     * @return array<int, int>
     */
    private function flatten(array $pairs): array
    {
        return array_merge(...array_map(fn (array $pair) => [$pair[0], $pair[1]], $pairs));
    }
}