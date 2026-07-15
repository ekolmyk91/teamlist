<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\CoffeeMeeting;
use App\User;
use Illuminate\Contracts\View\View;

class CoffeeConfirmationController extends Controller
{
    /**
     * Public signed endpoint hit from the Telegram bot's yes/no links.
     */
    public function __invoke(CoffeeMeeting $meeting, User $user, string $answer): View
    {
        abort_unless(in_array($answer, ['yes', 'no'], true), 404);
        abort_unless($meeting->hasParticipant((int) $user->id), 403);

        $meeting->applyAnswer((int) $user->id, $answer === 'yes');

        return view('coffee.confirmation', [
            'attended' => $answer === 'yes',
        ]);
    }
}
