<?php

/**
 * PHP-Konfiguration des Servers, auf dem die Tests laufen.
 *
 * Diese Tests prüfen keinen Anwendungscode, sondern die Umgebung: Der
 * häufigste Grund für "Upload hat nicht funktioniert" sind zu kleine
 * PHP-Limits. Der Browser bricht dann ohne brauchbare Fehlermeldung ab.
 *
 * Achtung: PHP-CLI und PHP-FPM/Apache verwenden oft unterschiedliche
 * php.ini-Dateien. Für die Werte, die im Web tatsächlich gelten, zusätzlich
 * `php artisan uploads:diagnose` über den Webserver-Benutzer laufen lassen
 * bzw. die Ausgabe von phpinfo() vergleichen.
 */

use App\Support\UploadLimits;

uses()->group('umgebung');

/** Videos bis 1 GB laut Oberfläche. */
const ERWARTETE_VIDEO_GROESSE = 1024 * 1024 * 1024;

/** Bis zu 10 Fotos gleichzeitig, jeweils bis ~10 MB. */
const ERWARTETE_FOTO_STAPEL_GROESSE = 10 * 10 * 1024 * 1024;

it('erlaubt Datei-Uploads überhaupt', function () {
    expect(filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOLEAN))
        ->toBeTrue('file_uploads ist in der php.ini deaktiviert.');
});

it('erlaubt Einzeldateien bis 1 GB (upload_max_filesize)', function () {
    $limit = UploadLimits::bytes(ini_get('upload_max_filesize'));

    expect($limit)->toBeGreaterThanOrEqual(
        ERWARTETE_VIDEO_GROESSE,
        sprintf(
            'upload_max_filesize = %s, für 1-GB-Videos werden mindestens 1024M gebraucht.',
            ini_get('upload_max_filesize')
        )
    );
});

it('erlaubt Requests, die größer als eine einzelne Datei sind (post_max_size)', function () {
    $post = UploadLimits::bytes(ini_get('post_max_size'));
    $upload = UploadLimits::bytes(ini_get('upload_max_filesize'));

    expect($post)->toBeGreaterThanOrEqual(
        $upload,
        sprintf(
            'post_max_size (%s) ist kleiner als upload_max_filesize (%s) – die größte erlaubte Datei kommt nie an.',
            ini_get('post_max_size'),
            ini_get('upload_max_filesize')
        )
    );

    expect($post)->toBeGreaterThanOrEqual(
        ERWARTETE_FOTO_STAPEL_GROESSE,
        sprintf('post_max_size = %s, für 10 Fotos à 10 MB zu klein.', ini_get('post_max_size'))
    );
});

it('erlaubt genug Dateien pro Request (max_file_uploads)', function () {
    expect((int) ini_get('max_file_uploads'))->toBeGreaterThanOrEqual(
        20,
        'max_file_uploads ist zu klein für Produkt-Galerien und Foto-Stapel.'
    );
});

it('hat genug Speicher für die Bildbearbeitung (memory_limit)', function () {
    $limit = UploadLimits::bytes(ini_get('memory_limit'));

    // -1 = unbegrenzt
    if ($limit < 0) {
        expect(true)->toBeTrue();

        return;
    }

    expect($limit)->toBeGreaterThanOrEqual(
        256 * 1024 * 1024,
        sprintf('memory_limit = %s, für Bildbearbeitung und S3-Uploads knapp.', ini_get('memory_limit'))
    );
});

it('bricht große Uploads nicht vorzeitig ab (max_input_time)', function () {
    $inputTime = (int) ini_get('max_input_time');

    // -1 bedeutet: max_execution_time wird verwendet.
    if ($inputTime === -1) {
        expect(true)->toBeTrue();

        return;
    }

    expect($inputTime)->toBeGreaterThanOrEqual(
        300,
        sprintf('max_input_time = %ds – langsame Uploads großer Videos brechen ab.', $inputTime)
    );
});

it('hat ein beschreibbares Upload-Temp-Verzeichnis', function () {
    $dir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();

    expect(is_dir($dir))->toBeTrue("Upload-Temp-Verzeichnis {$dir} existiert nicht.")
        ->and(is_writable($dir))->toBeTrue("Upload-Temp-Verzeichnis {$dir} ist nicht beschreibbar.");
});

it('hat im Upload-Temp-Verzeichnis Platz für ein 1-GB-Video', function () {
    $dir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
    $free = @disk_free_space($dir);

    if ($free === false) {
        expect(true)->toBeTrue();

        return;
    }

    expect($free)->toBeGreaterThan(
        ERWARTETE_VIDEO_GROESSE * 2,
        sprintf('Nur %s frei in %s – ein 1-GB-Video passt nicht zuverlässig hinein.', UploadLimits::human((int) $free), $dir)
    );
});

it('hat ein beschreibbares storage-Verzeichnis für die Metadaten-Entfernung', function () {
    $dir = storage_path('app/public/tmp');

    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    expect(is_dir($dir))->toBeTrue("{$dir} konnte nicht angelegt werden.")
        ->and(is_writable($dir))->toBeTrue("{$dir} ist nicht beschreibbar – ExifMetadataService schlägt fehl.");
});
