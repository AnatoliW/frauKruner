<?php

namespace Tests\Support;

use App\Category;
use App\Models\User;
use App\Models\Verification;
use App\Order;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Fabriken und Fake-Dateien für die Upload-Tests.
 */
class UploadTestHelpers
{
    public const ROLE_ADMIN = 1;

    public const ROLE_BUYER = 2;

    public const ROLE_SELLER = 3;

    /**
     * Setzt alle relevanten Disks auf Fakes und bestimmt die Default-Disk.
     *
     * Die Upload-Controller mischen `Storage::` (Default-Disk) und
     * `Storage::disk('s3')`. Deshalb werden die Tests gegen beide
     * Default-Disks gefahren – produktiv ist das s3.
     */
    public static function fakeDisks(string $default = 's3'): void
    {
        config(['filesystems.default' => $default]);

        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('s3');
    }

    public static function seller(array $attributes = []): User
    {
        return self::user(array_merge([
            'role_id' => self::ROLE_SELLER,
            // Die "verified"-Middleware lässt nur bestätigte Verkäufer durch.
            'verified' => 1,
        ], $attributes));
    }

    public static function buyer(array $attributes = []): User
    {
        return self::user(array_merge([
            'role_id' => self::ROLE_BUYER,
        ], $attributes));
    }

    public static function user(array $attributes = []): User
    {
        static $counter = 0;
        $counter++;

        return User::create(array_merge([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => "upload-test-{$counter}@example.test",
            'password' => 'secret-password',
            'email_verified_at' => now(),
            'status' => 1,
            'verified' => 0,
        ], $attributes));
    }

    public static function verificationFor(User $user, array $attributes = []): Verification
    {
        return Verification::create(array_merge([
            'user_id' => $user->id,
            'street' => 'Teststraße',
            'house_no' => '1',
            'city' => 'Berlin',
            'zip' => '10115',
            'date_of_birth' => '1990-01-01',
            'status' => 1,
        ], $attributes));
    }

    public static function category(array $attributes = []): Category
    {
        static $counter = 0;
        $counter++;

        return Category::create(array_merge([
            'name' => "Kategorie {$counter}",
            'slug' => "kategorie-{$counter}",
            'order' => 1,
        ], $attributes));
    }

    /**
     * Bestellung eines Käufers bei einem Verkäufer.
     */
    public static function order(User $vendor, ?User $buyer = null, array $attributes = []): Order
    {
        $buyer ??= self::buyer();

        return Order::create(array_merge([
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'first_name' => 'Käuferin',
            'last_name' => 'Test',
            'email' => $buyer->email,
            'total' => 49.90,
            'status' => 1,
            'payment_status' => 1,
        ], $attributes));
    }

    /**
     * Gültige Produktdaten ohne Dateien.
     *
     * @return array<string, mixed>
     */
    public static function productPayload(Category $category, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Testprodukt',
            'category' => $category->id,
            'details' => 'Beschreibung des Testprodukts',
            'selloption' => 1,
            'price' => 25,
            'shipping_cost' => 5,
        ], $overrides);
    }

    /**
     * Ein echtes, dekodierbares Bild in der gewünschten Größe.
     */
    public static function image(string $name, int $width = 400, int $height = 400): File
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Datei mit gefälschter Endung – der Inhalt ist kein Bild.
     * Deckt den Fall "PHP-Shell als .jpg getarnt" ab.
     */
    public static function disguisedPhpFile(string $name = 'shell.jpg'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-test');
        file_put_contents($path, "<?php echo 'pwned'; ?>\n");

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    /**
     * Video-Platzhalter der gewünschten Größe (in Kilobyte).
     */
    public static function video(string $name = 'clip.mp4', int $kilobytes = 2048): File
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'video/mp4');
    }

    /**
     * Video mit tatsächlich vorhandenen Bytes (nicht nur gemeldeter Größe).
     *
     * `UploadedFile::fake()->create()` meldet die Größe nur – `getSize()` gibt
     * den gewünschten Wert zurück, die Datei selbst ist leer. Für alles rund um
     * Größengrenzen braucht es eine Datei, deren `filesize()` wirklich stimmt.
     * Angelegt wird sie als Sparse-File, belegt also kaum Plattenplatz.
     */
    public static function realVideo(string $name = 'clip.mp4', int $megabytes = 1): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-test');
        $handle = fopen($path, 'wb');

        // ftyp-Box am Anfang, damit die Datei als MP4 erkannt wird.
        fwrite($handle, "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom");
        fseek($handle, $megabytes * 1024 * 1024 - 1);
        fwrite($handle, "\x00");
        fclose($handle);

        return new UploadedFile($path, $name, 'video/mp4', null, true);
    }

    /**
     * Upload, den PHP selbst verworfen hat.
     *
     * Genau so kommt der Request in der Anwendung an, wenn ein Video an einem
     * PHP-Limit scheitert: tmp_name ist leer, der Fehlercode steht im $_FILES-
     * Eintrag. `$request->hasFile('video')` liefert dann false – die Anwendung
     * darf daraus keinen Erfolg machen.
     *
     * @param  int  $error  UPLOAD_ERR_INI_SIZE (1) – Datei > upload_max_filesize
     *                      UPLOAD_ERR_FORM_SIZE (2) – Datei > MAX_FILE_SIZE im Formular
     *                      UPLOAD_ERR_PARTIAL (3) – Übertragung abgebrochen
     *                      UPLOAD_ERR_NO_TMP_DIR (6) – kein Temp-Verzeichnis
     *                      UPLOAD_ERR_CANT_WRITE (7) – Platte voll / keine Rechte
     */
    public static function rejectedVideo(int $error = UPLOAD_ERR_INI_SIZE, string $name = 'zu-gross.mp4'): UploadedFile
    {
        return new UploadedFile('', $name, 'video/mp4', $error, true);
    }

    /**
     * Klartext zu den PHP-Upload-Fehlercodes – für Testnamen und Meldungen.
     */
    public static function uploadErrorLabel(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'größer als upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'größer als MAX_FILE_SIZE im Formular',
            UPLOAD_ERR_PARTIAL => 'Übertragung abgebrochen',
            UPLOAD_ERR_NO_FILE => 'keine Datei gesendet',
            UPLOAD_ERR_NO_TMP_DIR => 'kein Temp-Verzeichnis',
            UPLOAD_ERR_CANT_WRITE => 'konnte nicht geschrieben werden',
            UPLOAD_ERR_EXTENSION => 'von einer PHP-Erweiterung gestoppt',
            default => "Fehlercode {$error}",
        };
    }

    /**
     * Markierung, die in den Metadaten der Testbilder steckt.
     */
    public const METADATA_MARKER = 'FRAUKRUNER-METADATEN-MARKIERUNG';

    /**
     * Gültiges JPEG mit einem Metadaten-Segment (JPEG-Comment, direkt hinter SOI).
     *
     * Steht stellvertretend für EXIF/GPS-Daten aus Handykameras: `exiftool -all=`
     * entfernt beides. Bleibt die Markierung nach dem Upload in der Datei,
     * hat die Metadaten-Entfernung nicht stattgefunden.
     */
    public static function jpegWithMetadata(string $marker = self::METADATA_MARKER): string
    {
        $image = imagecreatetruecolor(60, 60);
        ob_start();
        imagejpeg($image, null, 90);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);

        // FF FE <Länge inkl. der 2 Längenbytes> <Text> 00
        $segment = "\xFF\xFE".pack('n', strlen($marker) + 3).$marker."\x00";

        return substr($jpeg, 0, 2).$segment.substr($jpeg, 2);
    }

    public static function imageWithMetadata(string $name = 'mit-metadaten.jpg'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-test');
        file_put_contents($path, self::jpegWithMetadata());

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    /**
     * Steht `exiftool` auf diesem Rechner/Server zur Verfügung?
     */
    public static function exiftoolAvailable(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $output = [];
        $status = 1;
        @exec('exiftool -ver 2>/dev/null', $output, $status);

        return $status === 0;
    }
}
