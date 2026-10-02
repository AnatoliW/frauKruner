<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Beendet Pushs, deren Laufzeit abgelaufen ist.
 *
 * Damit das greift, braucht der Server genau einen Cron-Eintrag:
 * * * * * * cd /pfad/zum/projekt && php artisan schedule:run >> /dev/null 2>&1
 *
 * Stuendlich reicht: Ein Push laeuft ueber Tage, und die letzte angebrochene
 * Stunde geht zugunsten der Kundin aus.
 */
Schedule::command('boosts:expire')
    ->hourly()
    ->withoutOverlapping();

/*
 * Aus dem alten Projekt (fxxk, app/Console/Kernel.php) uebernommen.
 * 'dsiable:boosts' fehlt bewusst - das erledigt jetzt 'boosts:expire'.
 */
Schedule::command('last:login')->everyMinute()->withoutOverlapping();
Schedule::command('email:check')->everyMinute()->withoutOverlapping();
Schedule::command('shipped:email')->daily()->withoutOverlapping();
Schedule::command('delete:unpaidorder')->everyMinute()->withoutOverlapping();
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
Schedule::command('video:delete')->everyMinute()->withoutOverlapping();
// Schedule::command('media:process')->everyMinute()->withoutOverlapping();
// Schedule::command('videos:strip-metadata')->everyMinute()->withoutOverlapping();
