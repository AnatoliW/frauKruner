<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Concerns\UsesUploadSchema;
use Tests\Support\UploadTestHelpers;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

// Die Upload-Tests brauchen zusätzlich das Alt-Schema in der SQLite-Test-DB.
// (Als eigenes uses() pro Datei, da Pest keine überlappenden extend()-Pfade erlaubt.)
uses(UsesUploadSchema::class)->in('Feature/Uploads');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Kurzform für Tests\Support\UploadTestHelpers, damit die Upload-Tests
 * lesbar bleiben: uploads()->seller(), uploads()->video(), ...
 */
function uploads(): UploadTestHelpers
{
    return new UploadTestHelpers;
}

/**
 * Prüft, dass ein Request mit 403 abgelehnt wird – ohne die Fehlerseite zu
 * rendern. Das Frontend-Layout der Fehlerseiten fragt Tabellen ab, die im
 * schlanken Test-Schema nicht existieren; die Ausnahme selbst ist die
 * aussagekräftigere Zusicherung.
 */
function expectForbidden(Closure $request): void
{
    test()->withoutExceptionHandling();

    try {
        $request();
    } catch (HttpExceptionInterface $e) {
        expect($e->getStatusCode())->toBe(403);

        return;
    } finally {
        test()->withExceptionHandling();
    }

    test()->fail('Der Zugriff wurde nicht mit 403 abgelehnt.');
}

/**
 * Default-Disks, gegen die jeder Speicher-Test gefahren wird.
 * Produktiv läuft die Seite auf s3, lokal/Staging häufig auf local.
 */
dataset('disks', [
    'default disk = local' => 'local',
    'default disk = s3' => 's3',
]);
