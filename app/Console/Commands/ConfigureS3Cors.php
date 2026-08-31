<?php

namespace App\Console\Commands;

use Aws\Exception\AwsException;
use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Setzt die CORS-Regel, die Filament/FilePond zum Nachladen bereits
 * gespeicherter Bilder braucht.
 *
 * Hintergrund: Liegt ein FileUpload-Feld auf der S3-Disk, ist die Filament-
 * Sichtbarkeit "private", also wird eine presigned URL erzeugt. Ein <img>-Tag
 * darf die laden, das fetch() von FilePond (file-upload.js, server.load) nicht –
 * ohne "Access-Control-Allow-Origin" bricht der Browser ab und das Feld hängt
 * im Ladezustand. Genau dafür braucht der Bucket eine GET/HEAD-CORS-Regel.
 *
 *   php artisan s3:cors                                  (nur anzeigen, ändert nichts)
 *   php artisan s3:cors --origin=https://fraukruner.de    (weitere Origin ergänzen)
 *   php artisan s3:cors --apply                          (Regel wirklich schreiben)
 *
 * Bestehende Regeln bleiben erhalten: ersetzt wird ausschließlich die Regel mit
 * der ID "filament-file-upload", alles andere wird unverändert mitgeschrieben.
 */
class ConfigureS3Cors extends Command
{
    protected $signature = 's3:cors
                            {--apply : Die Regel wirklich in den Bucket schreiben (ohne diesen Schalter passiert nichts)}
                            {--origin=* : Zusätzlich erlaubte Origins, z. B. https://fraukruner.de}
                            {--disk=s3 : Name der Filesystem-Disk}';

    protected $description = 'Prüft und setzt die CORS-Regel des S3-Buckets für Filament-Datei-Uploads';

    /** ID der von diesem Befehl verwalteten Regel – nur sie wird ersetzt. */
    private const RULE_ID = 'filament-file-upload';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $bucket = (string) config("filesystems.disks.{$diskName}.bucket");

        if ($bucket === '') {
            $this->components->error("Für die Disk \"{$diskName}\" ist kein Bucket konfiguriert (AWS_BUCKET).");

            return self::FAILURE;
        }

        try {
            $disk = Storage::disk($diskName);
        } catch (Throwable $throwable) {
            $this->components->error("Disk \"{$diskName}\" nicht verfügbar: ".$throwable->getMessage());

            return self::FAILURE;
        }

        if (! $disk instanceof AwsS3V3Adapter) {
            $this->components->error("Disk \"{$diskName}\" ist keine S3-Disk.");

            return self::FAILURE;
        }

        $origins = $this->origins();

        if ($origins === []) {
            $this->components->error('Keine Origin ermittelbar. Bitte APP_URL setzen oder --origin=https://… angeben.');

            return self::FAILURE;
        }

        $this->components->info("Bucket: {$bucket} · Region: ".config("filesystems.disks.{$diskName}.region"));
        $this->line('  Origins: '.implode(', ', $origins));
        $this->newLine();

        $client = $disk->getClient();

        $existingRules = $this->readRules($client, $bucket);

        if ($existingRules === null) {
            return self::FAILURE;
        }

        $this->components->twoColumnDetail('Vorhandene CORS-Regeln', (string) count($existingRules));

        foreach ($existingRules as $rule) {
            $this->line('  · '.($rule['ID'] ?? 'ohne ID').': '
                .implode('/', Arr::wrap($rule['AllowedMethods'] ?? []))
                .' von '.implode(', ', Arr::wrap($rule['AllowedOrigins'] ?? [])));
        }

        $this->newLine();

        if ($this->isAlreadyCovered($existingRules, $origins)) {
            $this->components->info('Alle Origins sind bereits per GET erlaubt – keine Änderung nötig.');
            $this->hintOnMissingOrigin($existingRules, $origins);

            return self::SUCCESS;
        }

        $rules = $this->mergeRule($existingRules, $origins);

        $this->components->twoColumnDetail('Neue Konfiguration', (string) count($rules).' Regel(n)');
        $this->line($this->asJson($rules));
        $this->newLine();

        if (! $this->option('apply')) {
            $this->components->warn('Testlauf – es wurde nichts geändert. Mit --apply schreiben.');
            $this->line('  Alternativ das JSON oben in der AWS-Konsole unter');
            $this->line('  S3 → '.$bucket.' → Permissions → Cross-origin resource sharing (CORS) einfügen.');

            return self::SUCCESS;
        }

        try {
            $client->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => ['CORSRules' => $rules],
            ]);
        } catch (AwsException $exception) {
            $this->components->error('Schreiben fehlgeschlagen: '.$exception->getAwsErrorCode().' – '.$exception->getAwsErrorMessage());
            $this->line('  Der IAM-Benutzer braucht die Berechtigung "s3:PutBucketCORS" für diesen Bucket.');
            $this->line('  Ohne diese Berechtigung lässt sich das JSON oben in der AWS-Konsole eintragen.');

            return self::FAILURE;
        }

        $this->components->info('CORS-Regel gesetzt. Im Admin-Panel einmal hart neu laden (Cmd+Shift+R).');

        return self::SUCCESS;
    }

    /**
     * Erlaubte Origins: APP_URL plus die per --origin übergebenen Adressen.
     *
     * @return list<string>
     */
    private function origins(): array
    {
        $origins = [];

        foreach (array_merge([config('app.url')], (array) $this->option('origin')) as $candidate) {
            $origin = $this->normalizeOrigin((string) $candidate);

            if ($origin !== null) {
                $origins[] = $origin;
            }
        }

        return array_values(array_unique($origins));
    }

    /** Reduziert eine URL auf "schema://host[:port]" – genau das prüft S3. */
    private function normalizeOrigin(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * Liest die aktuelle Konfiguration. Ein noch nicht konfigurierter Bucket
     * meldet "NoSuchCORSConfiguration" – das ist kein Fehler, sondern eine
     * leere Liste.
     *
     * @return list<array<string, mixed>>|null  null bei echtem Fehler
     */
    private function readRules(mixed $client, string $bucket): ?array
    {
        try {
            $result = $client->getBucketCors(['Bucket' => $bucket]);

            return array_values((array) ($result['CORSRules'] ?? []));
        } catch (AwsException $exception) {
            if ($exception->getAwsErrorCode() === 'NoSuchCORSConfiguration') {
                return [];
            }

            $this->components->error('Lesen fehlgeschlagen: '.$exception->getAwsErrorCode().' – '.$exception->getAwsErrorMessage());
            $this->line('  Der IAM-Benutzer braucht die Berechtigung "s3:GetBucketCORS" für diesen Bucket.');

            return null;
        }
    }

    /**
     * Prüft, ob eine bestehende Regel bereits GET für alle Origins erlaubt.
     *
     * @param  list<array<string, mixed>>  $rules
     * @param  list<string>  $origins
     */
    private function isAlreadyCovered(array $rules, array $origins): bool
    {
        foreach ($origins as $origin) {
            $covered = false;

            foreach ($rules as $rule) {
                $methods = array_map('strtoupper', Arr::wrap($rule['AllowedMethods'] ?? []));

                if (! in_array('GET', $methods, true)) {
                    continue;
                }

                foreach (Arr::wrap($rule['AllowedOrigins'] ?? []) as $allowed) {
                    if ($this->originMatches((string) $allowed, $origin)) {
                        $covered = true;

                        break 2;
                    }
                }
            }

            if (! $covered) {
                return false;
            }
        }

        return true;
    }

    /** S3 erlaubt genau ein "*" als Platzhalter innerhalb einer Origin. */
    private function originMatches(string $allowed, string $origin): bool
    {
        if ($allowed === '*') {
            return true;
        }

        if (! str_contains($allowed, '*')) {
            return strcasecmp($allowed, $origin) === 0;
        }

        $pattern = '/^'.str_replace('\*', '.*', preg_quote($allowed, '/')).'$/i';

        return (bool) preg_match($pattern, $origin);
    }

    /**
     * Hängt die eigene Regel an bzw. ersetzt sie. Fremde Regeln bleiben
     * unangetastet.
     *
     * @param  list<array<string, mixed>>  $rules
     * @param  list<string>  $origins
     * @return list<array<string, mixed>>
     */
    private function mergeRule(array $rules, array $origins): array
    {
        $own = null;

        foreach ($rules as $rule) {
            if (($rule['ID'] ?? null) === self::RULE_ID) {
                $own = $rule;

                break;
            }
        }

        // Bereits erlaubte Origins der eigenen Regel behalten, damit ein Lauf
        // mit anderer APP_URL (z. B. lokaler Tunnel) nichts wegwirft.
        $merged = array_values(array_unique(array_merge(
            array_map('strval', Arr::wrap($own['AllowedOrigins'] ?? [])),
            $origins,
        )));

        $newRule = [
            'ID' => self::RULE_ID,
            'AllowedMethods' => ['GET', 'HEAD'],
            'AllowedOrigins' => $merged,
            'AllowedHeaders' => ['*'],
            'ExposeHeaders' => ['Content-Length', 'Content-Type', 'ETag'],
            'MaxAgeSeconds' => 3600,
        ];

        $rules = array_values(array_filter(
            $rules,
            static fn (array $rule): bool => ($rule['ID'] ?? null) !== self::RULE_ID,
        ));

        $rules[] = $newRule;

        return $rules;
    }

    /** @param  list<array<string, mixed>>  $rules */
    private function asJson(array $rules): string
    {
        return (string) json_encode(
            ['CORSRules' => $rules],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @param  list<string>  $origins
     */
    private function hintOnMissingOrigin(array $rules, array $origins): void
    {
        foreach ($rules as $rule) {
            if (in_array('*', Arr::wrap($rule['AllowedOrigins'] ?? []), true)) {
                $this->components->warn('Eine Regel erlaubt "*" als Origin – für den Livebetrieb besser auf die eigene Domain einschränken.');

                return;
            }
        }
    }
}
