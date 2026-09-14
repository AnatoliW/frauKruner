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
