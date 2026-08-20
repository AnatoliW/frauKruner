<?php

namespace Database\Seeders;

use App\Models\Boost;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Testdaten fuer die Rechnungsnummern der Hervorhebungen ("Push").
 *
 * Legt eine Testverkaeuferin und mehrere Pushs rund um den Stichtag aus
 * config('app.invoice_format_cutoff_date') an, damit sich alte (FKB<Zahlungs-ID>)
 * und neue Nummern (FKP-<Jahr>-<ID>) im Nutzerkonto und im Adminbereich
 * vergleichen lassen.
 *
 *   php artisan db:seed --class=PushInvoiceTestSeeder     Daten anlegen
 *   php artisan db:seed --class=PushInvoiceTestSeeder --no-interaction
 *
 * Der Seeder ist wiederholbar: Er loescht zuerst die Pushs der Testverkaeuferin
 * und legt sie danach neu an. Aufraeumen: siehe PushInvoiceTestSeeder::cleanup()
 * bzw. den Hinweis am Ende der Ausgabe.
 */
class PushInvoiceTestSeeder extends Seeder
{
    public const TEST_EMAIL = 'push-invoice-test@fraukruner.test';
    public const TEST_PASSWORD = 'PushTest2026!';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('Dieser Seeder legt Testdaten an und laeuft nicht in production.');

            return;
        }

        $cutoff = Carbon::parse(config('app.invoice_format_cutoff_date'));
        $seller = $this->seller();

        // Alte Testdaten entfernen, damit der Seeder wiederholbar bleibt.
        $this->cleanupBoosts($seller);

        $cases = [
            ['Alt: lange vor dem Stichtag',      $cutoff->copy()->subDays(30),        'TESTTRX-ALT-1',  'PAID'],
            ['Alt: eine Sekunde vor Stichtag',   $cutoff->copy()->subSecond(),        'TESTTRX-ALT-2',  'PAID'],
            ['Neu: exakt am Stichtag',           $cutoff->copy(),                     'TESTTRX-NEU-1',  'PAID'],
            ['Neu: nach dem Stichtag',           $cutoff->copy()->addDays(2),         'TESTTRX-NEU-2',  'PAID'],
            ['Neu: naechstes Jahr',              $cutoff->copy()->addYear(),          'TESTTRX-NEU-3',  'PAID'],
            ['Neu: ohne Zahlungs-ID',            $cutoff->copy()->addDays(3),         null,             'PAID'],
            ['Neu: offene Zahlung',              $cutoff->copy()->addDays(4),         null,             'PENDING'],
        ];

        $rows = [];

        foreach ($cases as [$label, $createdAt, $trnx, $status]) {
            $boost = $this->boost($seller, $createdAt);
            $this->payment($boost, $createdAt, $trnx, $status);

            $boost->refresh()->load('payment', 'payments');

            $rows[] = [
                $label,
                $boost->id,
                $createdAt->format('d.m.Y H:i:s'),
                $trnx ?? '—',
                $status,
                $boost->invoice_number,
            ];
        }

        $this->command?->newLine();
        $this->command?->info('Stichtag (app.invoice_format_cutoff_date): ' . $cutoff->format('d.m.Y H:i:s'));
        $this->command?->table(
            ['Fall', 'Boost-ID', 'angelegt am', 'Zahlungs-ID', 'Status', 'Rechnungs-Nr.'],
            $rows
        );

        $this->command?->info('Login Nutzerkonto: ' . self::TEST_EMAIL . ' / ' . self::TEST_PASSWORD);
        $this->command?->info('Nutzerkonto:  ' . route('seller.charges'));
        $this->command?->info('Admin (Filament): ' . url('/admin/boosts'));
        $this->command?->info('Admin (alt):      ' . route('admin.boost.invoice', $rows[0][1]));
        $this->command?->newLine();
        $this->command?->comment('Aufraeumen: php artisan push-invoice-test:cleanup');
    }

    private function seller(): User
    {
        $seller = User::withoutGlobalScopes()->where('email', self::TEST_EMAIL)->first();

        if (! $seller) {
            $seller = new User();
            $seller->email = self::TEST_EMAIL;
        }

        $seller->forceFill([
            'role_id' => 3,
            'name' => 'Testine',
            'last_name' => 'Pushmann',
            'username' => 'Push-Testkonto',
            'password' => Hash::make(self::TEST_PASSWORD),
            'email_verified_at' => now(),
            'status' => 1,
            'verified' => 1,
            'visibiliti_status' => 1,
        ])->save();

        return $seller;
    }

    private function boost(User $seller, Carbon $createdAt): Boost
    {
        $boost = Boost::create([
            'boostable_id' => $seller->id,
            'boostable_type' => User::class,
            'user_id' => $seller->id,
            'package_id' => 7,
            'base_price' => 10.00,
            'tax' => 1.90,
            'price' => 11.90,
            'status' => 1,
            'start_day' => $createdAt,
            'end_day' => $createdAt->copy()->addDays(12),
            'user_info' => [
                'f_name' => 'Testine',
                'l_name' => 'Pushmann',
                'street' => 'Schönhauser Allee',
                'house_no' => '163',
                'zip' => '10435',
                'federal_state' => 'Berlin',
                'email' => self::TEST_EMAIL,
                'vat_number' => '11/222/33333',
                'is_pay_vat' => '1',
                'vat_perchatage' => '19',
            ],
        ]);

        // created_at/updated_at steuern die Nummer, deshalb hart setzen.
        $boost->timestamps = false;
        $boost->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        $boost->timestamps = true;

        return $boost;
    }

    private function payment(Boost $boost, Carbon $createdAt, ?string $trnx, string $status): Payment
    {
        $payment = $boost->payment()->create([
            'payment_trnx_id' => $trnx,
            'payment_method' => 'test',
            'status' => $status,
            'tax' => 1.90,
            'amount' => 11.90,
        ]);

        $payment->timestamps = false;
        $payment->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $payment;
    }

    private function cleanupBoosts(User $seller): void
    {
        $boostIds = Boost::where('user_id', $seller->id)->pluck('id');

        if ($boostIds->isEmpty()) {
            return;
        }

        Payment::where('payable_type', Boost::class)->whereIn('payable_id', $boostIds)->delete();
        Boost::whereIn('id', $boostIds)->delete();

        $this->command?->comment('Alte Testdaten entfernt: ' . $boostIds->count() . ' Pushs.');
    }
}
