<?php

namespace App\Providers;

use App\Services\DateEvents\DateEventTriggerRegistry;
use App\Services\DateEvents\Triggers\BirthdayEventTrigger;
use App\Services\RandomCoffee\Contracts\CoffeeBotClientInterface;
use App\Services\RandomCoffee\Telegram\CoffeeBotClient;
use App\Services\Telegram\Contracts\TelegramClientInterface;
use App\Services\Telegram\TelegramClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TelegramClientInterface::class, function ($app) {
            return new TelegramClient(
                $app->make(HttpFactory::class),
                (string) config('services.telegram.api_url', 'https://api.telegram.org'),
                (string) config('services.telegram.bot_token', ''),
                (int) config('services.telegram.timeout', 10),
            );
        });

        $this->app->singleton(CoffeeBotClientInterface::class, function ($app) {
            return new CoffeeBotClient(
                $app->make(HttpFactory::class),
                (string) config('services.telegram.api_url', 'https://api.telegram.org'),
                (string) config('coffee.bot_token', ''),
                (int) config('services.telegram.timeout', 10),
            );
        });

        $this->app->bind(BirthdayEventTrigger::class, function () {
            return new BirthdayEventTrigger(
                (string) config('services.telegram.chat_id', ''),
                (string) config('events.templates.birthday', ''),
            );
        });

        $this->app->tag([
            BirthdayEventTrigger::class,
            // Add new triggers here, e.g. WorkAnniversaryEventTrigger::class
        ], 'date-events');

        $this->app->singleton(DateEventTriggerRegistry::class, function ($app) {
            return new DateEventTriggerRegistry($app->tagged('date-events'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
    }
}
