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

        // Auch Pushs, die ein Admin auf das Testkonto gesetzt hat (dort steht der
        // Admin in user_id, das Testkonto nur als boostable).
        $boostIds = Boost::where('user_id', $seller->id)
            ->orWhere(function ($query) use ($seller) {
                $query->where('boostable_type', User::class)
                    ->where('boostable_id', $seller->id);
            })
            ->pluck('id');

        $payments = Payment::where('payable_type', Boost::class)
            ->whereIn('payable_id', $boostIds)
            ->delete();

        $boosts = Boost::whereIn('id', $boostIds)->delete();

        $seller->forceFill([
            'boosted' => 0,
            'boost_start_date' => null,
            'boost_end_date' => null,
        ])->save();

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
