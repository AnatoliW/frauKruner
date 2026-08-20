<?php

namespace App\Console\Commands;

use App\Models\Boost;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\PushInvoiceTestSeeder;
use Illuminate\Console\Command;

/**
 * Entfernt die Testdaten aus PushInvoiceTestSeeder wieder aus der Datenbank.
 */
class PushInvoiceTestCleanup extends Command
{
    protected $signature = 'push-invoice-test:cleanup {--keep-user : Testkonto behalten, nur die Pushs loeschen}';

    protected $description = 'Loescht die Test-Pushs und das Testkonto des PushInvoiceTestSeeder.';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Laeuft nicht in production.');

            return self::FAILURE;
        }

        $seller = User::where('email', PushInvoiceTestSeeder::TEST_EMAIL)->first();

        if (! $seller) {
            $this->info('Keine Testdaten gefunden.');

            return self::SUCCESS;
        }

        $boostIds = Boost::where('user_id', $seller->id)->pluck('id');

        $payments = Payment::where('payable_type', Boost::class)
            ->whereIn('payable_id', $boostIds)
            ->delete();

        $boosts = Boost::whereIn('id', $boostIds)->delete();

        $this->info("Geloescht: {$boosts} Pushs, {$payments} Zahlungen.");

        if ($this->option('keep-user')) {
            $this->comment('Testkonto ' . PushInvoiceTestSeeder::TEST_EMAIL . ' bleibt bestehen.');

            return self::SUCCESS;
        }

        $seller->delete();
        $this->info('Testkonto ' . PushInvoiceTestSeeder::TEST_EMAIL . ' geloescht.');

        return self::SUCCESS;
    }
}
