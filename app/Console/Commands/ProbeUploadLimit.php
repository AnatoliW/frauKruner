<?php

namespace App\Console\Commands;

use App\Support\UploadLimits;
use Illuminate\Console\Command;

/**
 * Misst am laufenden Server, wie groß ein Video-Upload tatsächlich sein darf.
 *
 * Die Pest-Tests können das nicht: dort wird der Request im Speicher gebaut,
 * die Limits von Webserver und PHP kommen nie zum Tragen. Dieses Kommando
 * schickt echte HTTP-Uploads wachsender Größe an die Video-Upload-Route und
 * liest am Statuscode ab, wo die Kette bricht.
 *
 *   php artisan uploads:probe-limit
 *   php artisan uploads:probe-limit --url=https://www.fraukruner.de --sizes=100,500,1024
 *
 * Der Upload läuft bewusst OHNE Login: die Route liegt hinter der
 * auth-Middleware, der Request wird also vollständig entgegengenommen und
 * dann auf /login umgeleitet. Es entstehen keine Datensätze und es wird
 * keine Datei gespeichert.
 */
class ProbeUploadLimit extends Command
{
    protected $signature = 'uploads:probe-limit
                            {--url= : Basis-URL der Seite (Default: APP_URL)}
                            {--sizes=1,10,50,100,250,500,1024 : Zu testende Größen in MB, aufsteigend}
                            {--path=seller/dashboard/video/upload : Ziel-Route für den Upload}
                            {--timeout=1800 : Zeitlimit pro Upload in Sekunden}
                            {--insecure : TLS-Zertifikat nicht prüfen (nur für Staging)}
                            {--force : Ohne Rückfrage starten}';

    protected $description = 'Misst per echtem HTTP-Upload, wie groß ein Video am Server sein darf';

    private const DURCHGEKOMMEN = 'durchgekommen';

    private const VERWORFEN = 'verworfen';

    private const ABGEBROCHEN = 'abgebrochen';

    public function handle(): int
    {
        $base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        $target = $base.'/'.ltrim((string) $this->option('path'), '/');

        $sizes = collect(explode(',', (string) $this->option('sizes')))
            ->map(fn ($size) => (int) trim($size))
            ->filter(fn (int $size) => $size > 0)
            ->sort()
            ->values();

        if ($sizes->isEmpty()) {
            $this->components->error('Keine gültigen Größen angegeben.');

            return self::FAILURE;
        }

        $this->components->info('Upload-Limit ermitteln');
        $this->line('  Ziel:      '.$target);
        $this->line('  Größen:    '.$sizes->implode(' MB, ').' MB');
        $this->line('  Übertragen wird im schlechtesten Fall '.UploadLimits::human($sizes->sum() * 1024 * 1024).'.');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Starten?', true)) {
            return self::SUCCESS;
        }

        $session = $this->startSession($base);

        if ($session === null) {
            return self::FAILURE;
        }

        $rows = [];
        $groesstesOk = null;

        foreach ($sizes as $size) {
            $ergebnis = $this->probe($target, $size, $session);

            $rows[] = [
                $size.' MB',
                $ergebnis['status'] ?: '–',
                match ($ergebnis['urteil']) {
                    self::DURCHGEKOMMEN => '<fg=green>durchgekommen</>',
                    self::VERWORFEN => '<fg=red>verworfen</>',
                    default => '<fg=red>abgebrochen</>',
                },
                $ergebnis['dauer'] ? sprintf('%.1fs · %s/s', $ergebnis['dauer'], UploadLimits::human((int) $ergebnis['tempo'])) : '–',
                $ergebnis['erklaerung'],
            ];

            if ($ergebnis['urteil'] === self::DURCHGEKOMMEN) {
                $groesstesOk = $size;

                continue;
            }

            $this->table(['Größe', 'HTTP', 'Ergebnis', 'Dauer', 'Deutung'], $rows);
            $this->newLine();
            $this->components->error(sprintf(
                'Bei %d MB bricht die Kette ab. Größter durchgekommener Upload: %s.',
                $size,
                $groesstesOk ? $groesstesOk.' MB' : 'keiner'
            ));
            $this->hinweise();

            return self::FAILURE;
        }

        $this->table(['Größe', 'HTTP', 'Ergebnis', 'Dauer', 'Deutung'], $rows);
        $this->newLine();
        $this->components->info("Alle getesteten Größen bis {$groesstesOk} MB kommen am Server an.");
        $this->hinweise();

        return self::SUCCESS;
    }

    /**
     * Holt Session-Cookie und CSRF-Token von der Login-Seite.
     *
     * Das Token muss als Formularfeld mitgeschickt werden – nicht als Header.
     * Nur so verschwindet es zusammen mit den übrigen POST-Daten, wenn
     * post_max_size überschritten wird, und der 419 wird sichtbar.
     *
     * @return array{cookies:string, token:string}|null
     */
    private function startSession(string $base): ?array
    {
        $cookieJar = tempnam(sys_get_temp_dir(), 'upload-probe-cookies');

        $curl = curl_init($base.'/login');
        curl_setopt_array($curl, $this->basisOptionen() + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $cookieJar,
            CURLOPT_COOKIEFILE => $cookieJar,
        ]);

        $html = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $fehler = curl_error($curl);

        if ($html === false || $status !== 200) {
            $this->components->error(
                "Login-Seite nicht erreichbar (HTTP {$status}".($fehler ? ", {$fehler}" : '').').'
            );
            $this->line('  Läuft die Seite unter '.$base.'? Ggf. --url setzen.');
            @unlink($cookieJar);

            return null;
        }

        if (! preg_match('/name="_token"\s+value="([^"]+)"/', (string) $html, $treffer)
            && ! preg_match('/name="csrf-token"\s+content="([^"]+)"/', (string) $html, $treffer)) {
            $this->components->error('Kein CSRF-Token auf der Login-Seite gefunden.');
            @unlink($cookieJar);

            return null;
        }

        return ['cookies' => $cookieJar, 'token' => $treffer[1]];
    }

    /**
     * @param  array{cookies:string, token:string}  $session
     * @return array{status:int, urteil:string, erklaerung:string, dauer:float, tempo:float}
     */
    private function probe(string $target, int $megabytes, array $session): array
    {
        $datei = $this->sparseVideo($megabytes);

        $curl = curl_init($target);
        curl_setopt_array($curl, $this->basisOptionen() + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_COOKIEFILE => $session['cookies'],
            CURLOPT_COOKIEJAR => $session['cookies'],
            CURLOPT_TIMEOUT => (int) $this->option('timeout'),
            CURLOPT_POSTFIELDS => [
                '_token' => $session['token'],
                'order_id' => '0',
                'video' => new \CURLFile($datei, 'video/mp4', "probe-{$megabytes}mb.mp4"),
            ],
        ]);

        $this->output->write("  {$megabytes} MB … ");

        curl_exec($curl);

        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $dauer = (float) curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        $hochgeladen = (float) curl_getinfo($curl, CURLINFO_SIZE_UPLOAD);
        $ziel = (string) curl_getinfo($curl, CURLINFO_REDIRECT_URL);
        $curlFehler = curl_error($curl);

        @unlink($datei);

        [$urteil, $erklaerung] = $this->deuten($status, $ziel, $curlFehler);

        $this->line(match ($urteil) {
            self::DURCHGEKOMMEN => '<fg=green>ok</>',
            default => '<fg=red>abgelehnt</>',
        });

        return [
            'status' => $status,
            'urteil' => $urteil,
            'erklaerung' => $erklaerung,
            'dauer' => $dauer,
            'tempo' => $dauer > 0 ? $hochgeladen / $dauer : 0.0,
        ];
    }

    /**
     * @return array{0:string, 1:string}
     */
    private function deuten(int $status, string $ziel, string $curlFehler): array
    {
        if ($status === 0) {
            return [self::ABGEBROCHEN, 'Keine Antwort: '.($curlFehler ?: 'Verbindung abgebrochen').'. Meist ein Timeout im Webserver oder Proxy.'];
        }

        return match (true) {
            // Body kam vollständig an, CSRF bestanden, auth-Middleware leitet um.
            $status === 302 && str_contains($ziel, 'login') => [self::DURCHGEKOMMEN, 'Request vollständig angekommen.'],
            $status === 302 => [self::DURCHGEKOMMEN, 'Request angekommen, Weiterleitung nach '.($ziel ?: 'unbekannt').'.'],
            $status === 413 => [self::VERWORFEN, 'Body abgelehnt – entweder post_max_size (php.ini) oder das Webserver-Limit (nginx client_max_body_size / Apache LimitRequestBody).'],
            $status === 419 => [self::VERWORFEN, 'POST-Daten verworfen (CSRF-Token weg) – post_max_size in der php.ini ist kleiner als der Request.'],
            $status === 400 => [self::VERWORFEN, 'Webserver hat den Request verworfen (Body zu groß oder zu langsam).'],
            $status === 504 || $status === 502 => [self::VERWORFEN, 'Gateway-Timeout: Proxy/FPM bricht ab, bevor der Upload fertig ist.'],
            $status >= 500 => [self::VERWORFEN, "Serverfehler {$status} – Laravel-Log prüfen."],
            default => [self::VERWORFEN, "Unerwarteter Status {$status}."],
        };
    }

    /**
     * Sparse-File: meldet die volle Größe, belegt aber kaum Plattenplatz.
     * cURL überträgt trotzdem die vollen Bytes.
     */
    private function sparseVideo(int $megabytes): string
    {
        $pfad = tempnam(sys_get_temp_dir(), 'upload-probe');
        $handle = fopen($pfad, 'wb');

        fwrite($handle, "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom");
        fseek($handle, $megabytes * 1024 * 1024 - 1);
        fwrite($handle, "\x00");
        fclose($handle);

        return $pfad;
    }

    /**
     * @return array<int, mixed>
     */
    private function basisOptionen(): array
    {
        return [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT => 'fraukruner-upload-probe/1.0',
            CURLOPT_SSL_VERIFYPEER => ! $this->option('insecure'),
            CURLOPT_SSL_VERIFYHOST => $this->option('insecure') ? 0 : 2,
        ];
    }

    private function hinweise(): void
    {
        $this->line('  Gemessen wird das Limit von <options=bold>Webserver + post_max_size</>.');
        $this->line('  <options=bold>upload_max_filesize</> gilt zusätzlich pro Datei und ist von außen nicht');
        $this->line('  sichtbar: PHP verwirft dann nur die Datei, der Request selbst kommt durch.');
        $this->line('  Diesen Wert mit <options=bold>php artisan uploads:diagnose</> unter dem Webserver-Benutzer prüfen.');
    }
}
