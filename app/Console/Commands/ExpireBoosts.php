<?php

namespace App\Console\Commands;

use App\Models\Boost;
use App\Models\User;
use App\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Beendet Pushs, deren Laufzeit abgelaufen ist.
 *
 * Bis hierher gab es dafuer nichts: Boost::process() setzt beim Freischalten
 * ein Enddatum, Boost::end() wurde aber von niemandem aufgerufen. Ein gekaufter
 * Push lief damit unbegrenzt weiter. Dieser Befehl schliesst die Luecke und
 * laeuft stuendlich ueber den Scheduler (siehe routes/console.php).
 *
 * Er raeumt in zwei Durchgaengen auf:
 *
 * 1. Abgelaufene Boost-Datensaetze (status = 1, end_day vorbei).
 * 2. Profile und Produkte, die noch als gepusht markiert sind, obwohl ihr
 *    boost_end_date vorbei ist - etwa weil ein Admin das Kennzeichen von Hand
 *    gesetzt hat oder der zugehoerige Boost-Datensatz fehlt.
 */
class ExpireBoosts extends Command
{
    protected $signature = 'boosts:expire {--dry-run : Nur anzeigen, was beendet wuerde, ohne etwas zu aendern}';

    protected $description = 'Beendet abgelaufene Pushs von Profilen und Produkten.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();

        if ($dryRun) {
            $this->comment('Probelauf - es wird nichts gespeichert.');
            // Im Probelauf raeumt Durchgang 1 nichts weg, darum taucht dasselbe
            // Profil bzw. Produkt in Durchgang 2 noch einmal auf.
            $this->comment('Beide Durchgaenge koennen dieselben Datensaetze nennen.');
        }

        $boosts = $this->endExpiredBoosts($now, $dryRun);
        $orphans = $this->clearOrphanedFlags($now, $dryRun);

        if ($boosts === 0 && $orphans === 0) {
            $this->info('Keine abgelaufenen Pushs gefunden.');

            return self::SUCCESS;
        }

        $this->info("Beendet: {$boosts} Pushs, zusaetzlich {$orphans} verwaiste Kennzeichen zurueckgesetzt.");

        return self::SUCCESS;
    }

    /**
     * Durchgang 1: Boost-Datensaetze, deren Laufzeit vorbei ist.
     */
    private function endExpiredBoosts(Carbon $now, bool $dryRun): int
    {
        $expired = Boost::query()
            ->where('status', 1)
            ->whereNotNull('end_day')
            ->where('end_day', '<=', $now)
            ->orderBy('id')
            ->get();

        foreach ($expired as $boost) {
            $this->line(sprintf(
                '  Push #%d (%s #%d) - abgelaufen am %s',
                $boost->id,
                class_basename($boost->boostable_type),
                $boost->boostable_id,
                $boost->end_day->format('d.m.Y H:i')
            ));

            if (! $dryRun) {
                $boost->end();
            }
        }

        return $expired->count();
    }

    /**
     * Durchgang 2: Profile und Produkte, die ohne passenden Boost-Datensatz als
     * gepusht markiert sind. Geloeschte Produkte sind bewusst dabei - sie kommen
     * beim Wiederherstellen sonst mit einem ewigen Push zurueck.
     */
    private function clearOrphanedFlags(Carbon $now, bool $dryRun): int
    {
        $count = 0;

        $sources = [
            'Profil' => User::query(),
            'Produkt' => Product::withTrashed(),
        ];

        foreach ($sources as $label => $query) {
            $stale = $query
                ->where('boosted', 1)
                ->whereNotNull('boost_end_date')
                ->where('boost_end_date', '<=', $now)
                ->orderBy('id')
                ->get();

            foreach ($stale as $record) {
                // Dasselbe Sicherheitsnetz wie in Boost::end(): Ein zu frueh
                // stehendes boost_end_date darf keine Hervorhebung abschalten,
                // deren Boost-Datensatz noch laeuft.
                if (Boost::hasRunningBoost($record)) {
                    $this->line(sprintf(
                        '  %s #%d - uebersprungen, es laeuft noch ein Push.',
                        $label,
                        $record->getKey()
                    ));

                    continue;
                }

                $this->line(sprintf(
                    '  %s #%d - Kennzeichen ohne laufenden Push, Ende war %s',
                    $label,
                    $record->getKey(),
                    Carbon::parse($record->boost_end_date)->format('d.m.Y H:i')
                ));

                if (! $dryRun) {
                    // Start- und Enddatum bleiben als Verlauf stehen, siehe Boost::end().
                    $record->update(['boosted' => 0]);
                }

                $count++;
            }
        }

        return $count;
    }
}
