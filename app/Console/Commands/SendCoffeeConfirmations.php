<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RandomCoffee\ConfirmationSender;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class SendCoffeeConfirmations extends Command
{
    protected $signature = 'coffee:send-confirmations
                            {--date= : Meeting date in Y-m-d to ask about (defaults to yesterday in coffee.timezone)}';

    protected $description = 'Ask Random Coffee participants whether yesterday\'s meetings took place.';

    public function handle(ConfirmationSender $sender): int
    {
        $timezone = (string) config('coffee.timezone', 'UTC');
        $dateOption = (string) ($this->option('date') ?? '');
        $meetingDay = $dateOption !== ''
            ? CarbonImmutable::parse($dateOption, $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->subDay()->startOfDay();

        $sent = $sender->sendFor($meetingDay);

        $this->info(sprintf('Sent %d confirmation question(s) for %s.', $sent, $meetingDay->toDateString()));

        return self::SUCCESS;
    }
}