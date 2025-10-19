<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Comandos Artisan customizados
     */
    protected $commands = [
        Commands\ProcessarInteracoesErpCommand::class,
    ];

    /**
     * Define agendamento de comandos
     */
    protected function schedule(Schedule $schedule): void
    {
        // Processa interações ERP a cada 6 horas
        $schedule->command('csi:processar-erp')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/erp-processing.log'));

        // Processa tipos prioritários a cada hora
        $schedule->command('csi:processar-erp --force')
            ->hourly()
            ->between('8:00', '18:00') // Apenas em horário comercial
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Registra comandos do Closure
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}