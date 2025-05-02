<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Tutaj rejestrujesz własne komendy Artisan.
     */
    protected $commands = [
        // Komenda do wysyłki ostrzeżeń meteorologicznych i hydrologicznych
        \App\Console\Commands\SendWarningsEmails::class,
        // Komenda do sprawdzania i wysyłki ostrzeżeń temperaturowych
        \App\Console\Commands\CheckTemperatureAlerts::class,
    ];

    /**
     * Schedule your commands here.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ostrzeżenia meteorologiczne i hydrologiczne co 30 minut
        $schedule->command('send:warnings-emails')
            ->cron('*/30 * * * *')
            ->timezone('Europe/Warsaw');

        // Ostrzeżenia temperaturowe co 30 minut
        $schedule->command('check:temp-warnings')
            ->cron('*/30 * * * *')
            ->timezone('Europe/Warsaw');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Auto‐discovery katalogu Commands
        $this->load(__DIR__ . '/Commands');
    }
}
