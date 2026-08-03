<?php

namespace App\Console\Commands;

use App\Order;
use Illuminate\Console\Command;

/**
 * Rein lesende Pruefung: Auf welchen Belegen fehlt die PLZ der Hersteller*in?
 *
 * Das Kommando schreibt nichts. Es meldet, welche Belege nach der aufgeloesten
 * Fallback-Kette (Adresse -> Verifizierung) eine PLZ bekommen und bei welchen
 * die PLZ in keiner Quelle hinterlegt ist.
 */
class CheckSellerZip extends Command
{
    protected $signature = 'belege:plz-check
                            {--limit=0 : Nur die ersten N Belege pruefen (0 = alle)}
                            {--csv= : Ergebnis zusaetzlich als CSV in diese Datei schreiben}';

    protected $description = 'Prueft, bei welchen Belegen die PLZ der Hersteller*in fehlt (nur lesend)';

    public function handle(): int
    {
        $query = Order::query()
            ->whereNotNull('vendor_id')
            ->with(['vendor.address', 'vendor.verification'])
            ->orderBy('id');

        if ((int) $this->option('limit') > 0) {
            $query->limit((int) $this->option('limit'));
        }

        $ok = 0;
        $fehlend = [];

        $query->chunk(200, function ($orders) use (&$ok, &$fehlend) {
            foreach ($orders as $order) {
                $info = $order->seller_info;

                if (filled($info->zip ?? null)) {
                    $ok++;
                    continue;
                }

                $fehlend[] = [
                    'beleg_id' => $order->id,
                    'datum' => $order->created_at?->format('Y-m-d'),
                    'vendor_id' => $order->vendor_id,
                    'name' => trim(($info->f_name ?? '') . ' ' . ($info->l_name ?? '')),
                    'hat_adresse' => $order->vendor?->address ? 'ja' : 'nein',
                    'hat_verifizierung' => $order->vendor?->verification ? 'ja' : 'nein',
                ];
            }
        });

        $this->newLine();
        $this->info('Belege mit PLZ:  ' . $ok);
        $this->warn('Belege ohne PLZ: ' . count($fehlend));

        if ($fehlend === []) {
            $this->newLine();
            $this->info('Auf allen geprueften Belegen ist eine PLZ vorhanden.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['Beleg', 'Datum', 'Vendor', 'Name', 'Adresse', 'Verifizierung'],
            array_slice($fehlend, 0, 30)
        );

        if (count($fehlend) > 30) {
            $this->line('... ' . (count($fehlend) - 30) . ' weitere. Vollstaendige Liste via --csv=pfad.csv');
        }

        if ($pfad = $this->option('csv')) {
            $handle = fopen($pfad, 'w');
            fputcsv($handle, array_keys($fehlend[0]));
            foreach ($fehlend as $zeile) {
                fputcsv($handle, $zeile);
            }
            fclose($handle);
            $this->info('CSV geschrieben: ' . $pfad);
        }

        $this->newLine();
        $this->line('Bei diesen Hersteller*innen ist in keiner Quelle eine PLZ hinterlegt.');
        $this->line('Sie muss im Profil (Adressdaten) oder in der Verifizierung nachgetragen werden.');

        return self::SUCCESS;
    }
}
