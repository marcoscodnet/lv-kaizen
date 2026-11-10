<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Comandos registrados a mano. No se usa $this->load(__DIR__.'/Commands')
     * porque eso lee composer.json para resolver el namespace, y en este
     * proyecto composer.json no está (se renombró a composer_new.json).
     * Al agregar un comando nuevo en app/Console/Commands, sumarlo acá.
     */
    protected $commands = [
        \App\Console\Commands\AcreditarPagosSinAutorizacion::class,
        \App\Console\Commands\CerrarCajasVencidas::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Cierre automático de fin de día: a las 00:00 se cierran las cajas que
        // quedaron abiertas de días anteriores. Si un administrador reabre una
        // caja vieja, queda abierta hasta la medianoche siguiente.
        //
        // Se usa call() y no command(): command() lanza un proceso aparte con
        // proc_open, y el hosting la tiene deshabilitada ("The Process class
        // relies on proc_open..."), así que el comando nunca llegaba a correr.
        // call() + Artisan::call() lo ejecuta dentro del mismo proceso.
        // Cualquier tarea nueva del scheduler tiene que ir así también.
        $schedule->call(function () {
            \Illuminate\Support\Facades\Artisan::call('cajas:cerrar-vencidas');
        })
            ->name('cajas:cerrar-vencidas')
            ->dailyAt('00:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
