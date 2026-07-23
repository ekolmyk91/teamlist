<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\RandomCoffee\TelegramLinkHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Receives Random Coffee bot updates by webhook (used outside local, where the
 * app is reachable over HTTPS). Locally the same updates arrive by polling
 * (coffee:telegram-poll); both paths run through the shared TelegramLinkHandler.
 */
class CoffeeTelegramWebhookController extends Controller
{
    /**
     * Deduplication window: Telegram re-delivers an update until it gets a 2xx,
     * so a slow-but-successful handler could otherwise be processed twice.
     */
    private const SEEN_TTL_MINUTES = 60;

    public function __invoke(
        Request $request,
        CoffeeBotClientInterface $bot,
        TelegramLinkHandler $handler,
    ): Response {
        $secret = (string) config('coffee.webhook_secret', '');
        $presented = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        // No secret configured means the endpoint is not meant to be live yet;
        // never accept unauthenticated updates.
        if ($secret === '' || ! hash_equals($secret, $presented)) {
            abort(403);
        }

        $update = $request->all();
        $updateId = (int) ($update['update_id'] ?? 0);

        // Cache::add is an atomic check-and-set: false means we are already
        // handling (or have handled) this update_id, so skip the duplicate
        // delivery and still answer 2xx.
        if ($updateId > 0 && ! Cache::add($this->seenKey($updateId), true, now()->addMinutes(self::SEEN_TTL_MINUTES))) {
            return response()->noContent();
        }

        try {
            $reply = $handler->handle($update);
        } catch (Throwable $e) {
            // The stateful work (linking, /stop) failed — most likely a
            // transient error (DB down, deadlock). Release the dedup key and
            // answer non-2xx so Telegram redelivers the update rather than
            // dropping it silently.
            report($e);
            $this->releaseSeen($updateId);

            return response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Delivering the reply is best-effort: the important work is already
        // committed. A send failure (e.g. the user blocked the bot) must NOT
        // make Telegram redeliver an update we have fully processed, so it is
        // logged and swallowed with a 2xx.
        if ($reply !== null) {
            try {
                $bot->send($reply);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->noContent();
    }

    private function seenKey(int $updateId): string
    {
        return 'coffee:telegram-webhook:seen:' . $updateId;
    }

    private function releaseSeen(int $updateId): void
    {
        if ($updateId > 0) {
            Cache::forget($this->seenKey($updateId));
        }
    }
}