<?php

declare(strict_types=1);

namespace App\Services\RandomCoffee;

final class PairMatcher
{
    /**
     * Upper bound on backtracking recursion steps. A feasible pairing is
     * normally found almost immediately; the budget only cuts off the
     * exponential exhaustive search on unsatisfiable or near-unsatisfiable
     * constraint graphs (then the greedy fallback takes over).
     */
    private const MAX_SEARCH_STEPS = 20000;

    /**
     * Split participants into random pairs, avoiding recently met couples.
     *
     * Falls back to a greedy pass that allows repeats when no repeat-free
     * combination exists (or the search budget is exhausted): a repeated
     * coffee beats no coffee at all. With an odd number of participants one
     * random person skips the cycle.
     *
     * @param array<int, int> $userIds participating user ids
     * @param array<int, string> $recentPairs pair keys built with self::pairKey()
     * @return array<int, array{0: int, 1: int}>
     */
    public function match(array $userIds, array $recentPairs): array
    {
        $userIds = array_values(array_unique($userIds));

        if (count($userIds) < 2) {
            return [];
        }

        shuffle($userIds);

        if (count($userIds) % 2 !== 0) {
            array_pop($userIds);
        }

        $recent = array_flip($recentPairs);
        $steps = 0;
        $pairs = $this->matchAvoidingRecent($userIds, $recent, $steps);

        return $pairs ?? $this->greedyWithRepeats($userIds, $recent);
    }

    public static function pairKey(int $userA, int $userB): string
    {
        return min($userA, $userB) . '-' . max($userA, $userB);
    }

    /**
     * Budget-limited backtracking search for a pairing without recent repeats.
     *
     * @param array<int, int> $userIds
     * @param array<string, int> $recent
     * @return array<int, array{0: int, 1: int}>|null
     */
    private function matchAvoidingRecent(array $userIds, array $recent, int &$steps): ?array
    {
        if ($userIds === []) {
            return [];
        }

        if (++$steps > self::MAX_SEARCH_STEPS) {
            return null;
        }

        $first = array_shift($userIds);

        foreach ($userIds as $index => $candidate) {
            if (isset($recent[self::pairKey($first, $candidate)])) {
                continue;
            }

            $rest = $userIds;
            unset($rest[$index]);

            $pairs = $this->matchAvoidingRecent(array_values($rest), $recent, $steps);

            if ($pairs !== null) {
                array_unshift($pairs, [$first, $candidate]);

                return $pairs;
            }

            if ($steps > self::MAX_SEARCH_STEPS) {
                return null;
            }
        }

        return null;
    }

    /**
     * Linear-time pairing that prefers non-recent partners but accepts a
     * repeat when nothing else is left.
     *
     * @param array<int, int> $userIds
     * @param array<string, int> $recent
     * @return array<int, array{0: int, 1: int}>
     */
    private function greedyWithRepeats(array $userIds, array $recent): array
    {
        $pairs = [];

        while (count($userIds) >= 2) {
            $first = array_shift($userIds);
            $partnerIndex = array_key_first($userIds);

            foreach ($userIds as $index => $candidate) {
                if (! isset($recent[self::pairKey($first, $candidate)])) {
                    $partnerIndex = $index;
                    break;
                }
            }

            $pairs[] = [$first, $userIds[$partnerIndex]];
            unset($userIds[$partnerIndex]);
            $userIds = array_values($userIds);
        }

        return $pairs;
    }
}
