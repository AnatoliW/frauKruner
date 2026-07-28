<?php

namespace App\Console\Commands;

use App\Support\UploadLimits;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\TableSeparator;
use Throwable;

/**
 * Prüft auf dem laufenden Server alles, was ein Foto-/Video-Upload braucht:
 * PHP-Limits, Erweiterungen, exiftool/ffmpeg, Schreibrechte, Storage-Disks
 * und die Datenbankspalten, in die der Upload-Code schreibt.
 *
 *   php artisan uploads:diagnose
 *   php artisan uploads:diagnose --write     (legt Testdateien an und löscht sie wieder)
 *
 * Rückgabewert 1, sobald mindestens eine Prüfung fehlschlägt – so lässt sich
 * der Aufruf in ein Deploy-Skript hängen.
 */
class DiagnoseUploads extends Command
{
    protected $signature = 'uploads:diagnose
                            {--write : Echten Schreib-/Lese-/Löschtest auf den Storage-Disks ausführen}
                            {--video-size=1024 : Erwartete maximale Videogröße in MB}';

    protected $description = 'Prüft Server-Konfiguration und Infrastruktur für Foto- und Video-Uploads';

    private const OK = 'OK';

    private const WARN = 'WARNUNG';

    private const FAIL = 'FEHLER';

    /** @var list<array{0:string,1:string,2:string,3:string}> */
    private array $results = [];

    public function handle(): int
    {
        $this->components->info('Upload-Diagnose für '.config('app.name').' ('.app()->environment().')');
        $this->line('  PHP '.PHP_VERSION.' · SAPI: '.PHP_SAPI);
        $this->line('  php.ini: '.(php_ini_loaded_file() ?: 'keine geladen'));
        $this->newLine();

        $this->checkPhpLimits();
        $this->checkExtensions();
        $this->checkExternalTools();
        $this->checkDirectories();
        $this->checkDisks();
        $this->checkDatabaseColumns();

        $this->render();

        $failures = collect($this->results)->where(0, self::FAIL)->count();
        $warnings = collect($this->results)->where(0, self::WARN)->count();

        $this->newLine();

        if ($failures > 0) {
            $this->components->error("{$failures} Prüfung(en) fehlgeschlagen, {$warnings} Warnung(en).");

            return self::FAILURE;
        }

        if ($warnings > 0) {
            $this->components->warn("Alle Pflichtprüfungen bestanden, {$warnings} Warnung(en).");

            return self::SUCCESS;
        }

        $this->components->info('Alle Prüfungen bestanden.');

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------
    // Prüfungen
    // -----------------------------------------------------------------

    private function checkPhpLimits(): void
    {
        $videoBytes = ((int) $this->option('video-size')) * 1024 * 1024;

        if (PHP_SAPI === 'cli') {
            $this->add(self::WARN, 'PHP-Limits', 'Gemessen unter PHP-CLI', 'Web-Requests nutzen oft eine andere php.ini (PHP-FPM/Apache). Werte mit phpinfo() gegenprüfen.');
        }

        $this->add(
            filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN) ? self::OK : self::FAIL,
            'PHP-Limits',
            'file_uploads = '.(ini_get('file_uploads') ?: '0'),
            'Muss aktiviert sein.'
        );

        $upload = UploadLimits::bytes(ini_get('upload_max_filesize'));
        $this->add(
            $upload >= $videoBytes ? self::OK : self::FAIL,
            'PHP-Limits',
            'upload_max_filesize = '.ini_get('upload_max_filesize'),
            'Benötigt: mindestens '.UploadLimits::human($videoBytes)
        );

        $post = UploadLimits::bytes(ini_get('post_max_size'));
        $this->add(
            $post >= $upload && $post >= $videoBytes ? self::OK : self::FAIL,
            'PHP-Limits',
            'post_max_size = '.ini_get('post_max_size'),
            'Muss >= upload_max_filesize sein, sonst wird der Request stillschweigend verworfen.'
        );

        $memory = UploadLimits::bytes(ini_get('memory_limit'));
        $this->add(
            $memory < 0 || $memory >= 256 * 1024 * 1024 ? self::OK : self::WARN,
            'PHP-Limits',
            'memory_limit = '.ini_get('memory_limit'),
            'Empfohlen: mindestens 256M.'
        );

        $files = (int) ini_get('max_file_uploads');
        $this->add(
            $files >= 20 ? self::OK : self::WARN,
            'PHP-Limits',
            'max_file_uploads = '.$files,
            'Empfohlen: mindestens 20 (Produktgalerie und Foto-Stapel).'
        );

        $inputTime = (int) ini_get('max_input_time');
        $this->add(
            $inputTime === -1 || $inputTime >= 300 ? self::OK : self::WARN,
            'PHP-Limits',
            'max_input_time = '.$inputTime,
            'Empfohlen: mindestens 300s, sonst brechen langsame Uploads ab.'
        );

        $execTime = (int) ini_get('max_execution_time');
        $this->add(
            $execTime === 0 || $execTime >= 300 ? self::OK : self::WARN,
            'PHP-Limits',
            'max_execution_time = '.$execTime,
            'VideoUpload() setzt zwar set_time_limit(1000), das greift aber nicht im Safe-Mode/mit manchen FPM-Konfigurationen.'
        );

        // Das kleinere der beiden Limits ist die Obergrenze für ein einzelnes
        // Video. post_max_size muss zusätzlich die übrigen Formularfelder
        // tragen, deshalb der Sicherheitsabzug.
        $effektiv = min($upload, max(0, $post - 1024 * 1024));

        $this->add(
            $effektiv >= $videoBytes ? self::OK : self::FAIL,
            'PHP-Limits',
            'Effektiv größtes Video: '.UploadLimits::human($effektiv),
            sprintf(
                'Kleinster Wert aus upload_max_filesize (%s) und post_max_size (%s) minus Formular-Overhead. '
                .'Die Oberfläche verspricht %s.',
                ini_get('upload_max_filesize'),
                ini_get('post_max_size'),
                UploadLimits::human($videoBytes)
            )
        );
    }

    private function checkExtensions(): void
    {
        foreach ([
            'fileinfo' => ['required' => true, 'why' => 'Wird von den Validierungsregeln image/mimes gebraucht.'],
            'gd' => ['required' => true, 'why' => 'Bildverarbeitung.'],
            'exif' => ['required' => false, 'why' => 'Nur zum Auslesen; das Entfernen macht exiftool.'],
        ] as $extension => $meta) {
            $loaded = extension_loaded($extension);

            $this->add(
                $loaded ? self::OK : ($meta['required'] ? self::FAIL : self::WARN),
                'PHP-Erweiterungen',
                $extension.($loaded ? ' geladen' : ' FEHLT'),
                $meta['why']
            );
        }
    }

    private function checkExternalTools(): void
    {
        if (! function_exists('exec')) {
            $this->add(self::FAIL, 'Externe Tools', 'exec() ist deaktiviert', 'ExifMetadataService kann exiftool nicht aufrufen – Metadaten bleiben in den Bildern.');

            return;
        }

        $exiftool = $this->toolVersion('exiftool -ver');
        $this->add(
            $exiftool !== null ? self::OK : self::FAIL,
            'Externe Tools',
            'exiftool '.($exiftool ?? 'NICHT GEFUNDEN'),
            'Ohne exiftool werden EXIF-/GPS-Daten NICHT entfernt – ExifMetadataService meldet trotzdem Erfolg.'
        );

        $ffmpeg = $this->toolVersion('ffmpeg -version');
        $this->add(
            $ffmpeg !== null ? self::OK : self::WARN,
            'Externe Tools',
            'ffmpeg '.($ffmpeg ? Str::before($ffmpeg, ' ') : 'NICHT GEFUNDEN'),
            'Nur nötig, wenn StripeVideoMetaData wieder aktiviert wird (derzeit in PagesController auskommentiert).'
        );
    }

    private function checkDirectories(): void
    {
        $tmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();

        $this->add(
            is_dir($tmpDir) && is_writable($tmpDir) ? self::OK : self::FAIL,
            'Verzeichnisse',
            'Upload-Temp: '.$tmpDir,
            'Muss existieren und für den Webserver-Benutzer beschreibbar sein.'
        );

        $free = @disk_free_space($tmpDir);
        if ($free !== false) {
            $needed = ((int) $this->option('video-size')) * 1024 * 1024 * 2;
            $this->add(
                $free > $needed ? self::OK : self::WARN,
                'Verzeichnisse',
                'Freier Platz in '.$tmpDir.': '.UploadLimits::human($free),
                'Empfohlen: mindestens die doppelte maximale Videogröße.'
            );
        }

        foreach ([
            storage_path('app/public/tmp') => 'Zwischenablage von ExifMetadataService',
            storage_path('framework/cache') => 'Framework-Cache',
            storage_path('logs') => 'Logs',
        ] as $dir => $why) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $this->add(
                is_dir($dir) && is_writable($dir) ? self::OK : self::FAIL,
                'Verzeichnisse',
                str_replace(base_path().'/', '', $dir),
                $why
            );
        }

        $leftovers = is_dir(storage_path('app/public/tmp'))
            ? count(glob(storage_path('app/public/tmp').'/*') ?: [])
            : 0;

        if ($leftovers > 0) {
            $this->add(
                self::WARN,
                'Verzeichnisse',
                "storage/app/public/tmp: {$leftovers} Restdatei(en)",
                'Abgebrochene Metadaten-Entfernungen lassen Dateien liegen – regelmäßig aufräumen.'
            );
        }
    }

    private function checkDisks(): void
    {
        $default = config('filesystems.default');

        $this->add(
            is_string($default) && config("filesystems.disks.{$default}") !== null ? self::OK : self::FAIL,
            'Storage',
            "Default-Disk: {$default}",
            'FILESYSTEM_DISK in der .env. Achtung: eine alte .env mit FILESYSTEM_DRIVER wird nicht mehr gelesen.'
        );

        // Der Upload-Code spricht an mehreren Stellen fest "s3" an
        // (PagesController::VideoUpload, ProductsController::update).
        $s3Configured = filled(config('filesystems.disks.s3.key'))
            && filled(config('filesystems.disks.s3.secret'))
            && filled(config('filesystems.disks.s3.bucket'));

        $this->add(
            $s3Configured ? self::OK : ($default === 's3' ? self::FAIL : self::WARN),
            'Storage',
            's3-Zugangsdaten '.($s3Configured ? 'gesetzt' : 'unvollständig'),
            'Teile des Upload-Codes verwenden Storage::disk(\'s3\') fest verdrahtet, auch wenn die Default-Disk eine andere ist.'
        );

        if ($default !== 's3' && $s3Configured) {
            $this->add(
                self::WARN,
                'Storage',
                "Default-Disk ({$default}) != s3",
                'Videos werden auf der Default-Disk abgelegt, aber auf s3 gesucht/gelöscht – alte Videos bleiben liegen.'
            );
        }

        if (! $this->option('write')) {
            $this->add(self::WARN, 'Storage', 'Schreibtest übersprungen', 'Mit --write ausführen, um Datei anlegen/lesen/löschen wirklich zu prüfen.');

            return;
        }

        $disks = array_unique(array_filter([$default, $s3Configured ? 's3' : null]));

        foreach ($disks as $disk) {
            $this->roundTrip($disk);
        }
    }

    private function roundTrip(string $disk): void
    {
        $path = 'upload-diagnose/test-'.Str::random(12).'.txt';
        $content = 'upload-diagnose '.now()->toIso8601String();

        try {
            if (! Storage::disk($disk)->put($path, $content)) {
                $this->add(self::FAIL, 'Storage', "[{$disk}] Schreiben", "Datei {$path} konnte nicht angelegt werden.");

                return;
            }

            $readBack = Storage::disk($disk)->get($path);

            if ($readBack !== $content) {
                $this->add(self::FAIL, 'Storage', "[{$disk}] Lesen", 'Inhalt stimmt nicht mit dem Geschriebenen überein.');
            } else {
                $this->add(self::OK, 'Storage', "[{$disk}] Schreiben/Lesen", 'Round-Trip erfolgreich.');
            }

            Storage::disk($disk)->delete($path);

            $this->add(
                Storage::disk($disk)->exists($path) ? self::FAIL : self::OK,
                'Storage',
                "[{$disk}] Löschen",
                'Testdatei wurde wieder entfernt.'
            );
        } catch (Throwable $e) {
            $this->add(self::FAIL, 'Storage', "[{$disk}] Round-Trip", get_class($e).': '.$e->getMessage());
        }
    }

    private function checkDatabaseColumns(): void
    {
        // Spalten, in die der Upload-Code schreibt. Fehlt eine davon,
        // endet der Upload in einem 500er statt in einer Fehlermeldung.
        $required = [
            'products' => ['image', 'meta_remove_status'],
            'images' => ['product_id', 'image', 'nsfw', 'meta_remove_status'],
            'orders' => ['video', 'video_uploaded_at'],
            'orderimages' => ['order_id', 'image', 'meta_remove_status'],
            'profiles' => ['profile_img', 'meta_remove_status'],
            'verifications' => ['person_id_shot_img', 'id_card_front_img', 'id_card_back_img', 'status'],
        ];

        try {
            foreach ($required as $table => $columns) {
                if (! Schema::hasTable($table)) {
                    $this->add(self::FAIL, 'Datenbank', "Tabelle {$table} fehlt", 'Wird vom Upload-Code verwendet.');

                    continue;
                }

                $missing = array_values(array_filter(
                    $columns,
                    fn (string $column) => ! Schema::hasColumn($table, $column)
                ));

                $this->add(
                    $missing === [] ? self::OK : self::FAIL,
                    'Datenbank',
                    $missing === []
                        ? "{$table}: alle Spalten vorhanden"
                        : "{$table}: fehlende Spalte(n) ".implode(', ', $missing),
                    $missing === [] ? '' : 'Der Upload-Code schreibt in diese Spalte(n) und läuft sonst in einen SQL-Fehler.'
                );
            }
        } catch (Throwable $e) {
            $this->add(self::FAIL, 'Datenbank', 'Verbindung fehlgeschlagen', $e->getMessage());
        }
    }

    // -----------------------------------------------------------------
    // Ausgabe
    // -----------------------------------------------------------------

    private function toolVersion(string $command): ?string
    {
        $output = [];
        $status = 1;
        @exec($command.' 2>/dev/null', $output, $status);

        if ($status !== 0 || $output === []) {
            return null;
        }

        return trim((string) $output[0]);
    }

    private function add(string $status, string $group, string $check, string $note): void
    {
        $this->results[] = [$status, $group, $check, $note];
    }

    private function render(): void
    {
        $rows = [];
        $lastGroup = null;

        foreach ($this->results as [$status, $group, $check, $note]) {
            if ($lastGroup !== null && $lastGroup !== $group) {
                $rows[] = new TableSeparator;
            }
            $lastGroup = $group;

            $rows[] = [
                match ($status) {
                    self::OK => '<fg=green>OK</>',
                    self::WARN => '<fg=yellow>WARN</>',
                    default => '<fg=red>FEHLER</>',
                },
                $group,
                $check,
                $status === self::OK ? '' : $note,
            ];
        }

        $this->table(['', 'Bereich', 'Prüfung', 'Hinweis'], $rows);
    }
}
