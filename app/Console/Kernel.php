<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Extracción diaria de datos de Watts a las 2:00 AM
        $schedule->command('watts:extract --type=all --async')
            ->dailyAt('02:00')
            ->timezone('America/Santiago')
            ->name('Watts Daily Extraction')
            ->withoutOverlapping()
            ->onOneServer()
            ->emailOutputOnFailure(env('ADMIN_EMAIL', 'admin@example.com'))
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // También puedes configurar extracciones específicas en diferentes horarios
        // Por ejemplo, productos cada 6 horas:
        // $schedule->command('watts:extract --type=products --async')
        //     ->everySixHours()
        //     ->timezone('America/Santiago');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\NotificationAlertCommand::class,
        \App\Console\Commands\SapConnectionTestCommand::class,
    ];
}
