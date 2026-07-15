<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\CoffeeMeeting;
use App\Services\RandomCoffee\MeetingGenerator;
use App\Services\RandomCoffee\MeetingNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class GenerateCoffeeMeetings extends Command
{
    protected $signature = 'coffee:generate-meetings
                            {--date= : Generation date in Y-m-d (defaults to today in coffee.timezone)}
                            {--auto : Scheduler mode - run only when settings say it is time}
                            {--dry-run : Print the planned pairs without saving or notifying}';

    protected $description = 'Match employees into Random Coffee pairs and notify them via the Telegram bot.';

    public function handle(MeetingGenerator $generator, MeetingNotifier $notifier): int
    {
        $timezone = (string) config('coffee.timezone', 'UTC');
        $dateOption = (string) ($this->option('date') ?? '');
        $date = $dateOption !== ''
            ? CarbonImmutable::parse($dateOption, $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfDay();

        if ($this->option('auto') && ! $generator->shouldRunOn($date)) {
            $this->line(sprintf('Skipped: settings do not require a run on %s.', $date->toDateString()));

            return self::SUCCESS;
        }

        if ($generator->alreadyGeneratedFor($date)) {
            $this->warn(sprintf('Meetings for the cycle starting %s already exist.', $date->toDateString()));

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $plan = $generator->plan($date);
            $this->info(sprintf('DRY-RUN: %d pair(s) for %s', $plan->count(), $date->toDateString()));

            foreach ($plan as $item) {
                $this->line(sprintf(
                    '  - #%d + #%d on %s (%s)',
                    $item['user_one_id'],
                    $item['user_two_id'],
                    $item['scheduled_at']->format('Y-m-d H:i'),
                    $item['meeting_url'],
                ));
            }

            return self::SUCCESS;
        }

        $meetings = $generator->generate($date);
        $sent = $meetings->sum(fn (CoffeeMeeting $meeting) => $notifier->notify($meeting));

        $this->info(sprintf(
            'Generated %d meeting(s) for %s, sent %d Telegram notification(s).',
            $meetings->count(),
            $date->toDateString(),
            $sent,
        ));

        return self::SUCCESS;
    }
}