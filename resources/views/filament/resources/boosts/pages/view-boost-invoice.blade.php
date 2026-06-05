<x-filament-panels::page>
    @php
        $boost = $this->getRecord();
        $payment = $boost->payment ?? $boost->payments->first();
        $tax = $payment?->tax ?? 0;

        $fmt = static function ($value): string {
            return sprintf('%.2f €', (float) ($value ?? 0));
        };

        $buyerFirstName = $boost->user_info->f_name ?? $boost->user?->name ?? '';
        $buyerLastName = $boost->user_info->l_name ?? $boost->user?->last_name ?? '';

        $cutoffDate = \Carbon\Carbon::parse(config('app.invoice_format_cutoff_date'));
        $useOldFormat = $boost->created_at->lt($cutoffDate) && $payment && $payment->payment_trnx_id;
    @endphp

    <style>
        .invoice-page {
            font-size: 13px;
            line-height: 1.45;
        }

        .invoice-section {
            border: 1px solid #81838B;
            margin-bottom: 18px;
            padding: 14px 16px;
        }

        .invoice-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 24px;
            font-weight: 500;
            margin: 0 0 14px;
        }

        .print-btn {
            border: 1px solid #1f2937;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 12px;
            line-height: 1.4;
            cursor: pointer;
            background: transparent;
        }

        .invoice-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 12px;
        }

        .label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .invoice-table-wrap {
            overflow-x: auto;
            margin-top: 8px;
            scrollbar-width: none;
        }

        .invoice-table-wrap::-webkit-scrollbar {
            display: none;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .invoice-table thead th {
            text-align: left;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        .invoice-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            vertical-align: top;
        }

        .muted-note {
            margin-top: 8px;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .invoice-grid {
                grid-template-columns: 1fr;
            }

            .invoice-section-title {
                font-size: 20px;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .invoice-page,
            .invoice-page * {
                visibility: visible;
            }

            .invoice-page {
                position: absolute;
                inset: 0;
                background: #fff;
                font-size: 12px;
                line-height: 1.4;
                padding: 0;
                margin: 0;
            }

            .invoice-section {
                border: 1px solid #81838B;
                margin: 0 0 12px;
                padding: 10px 12px;
                break-inside: avoid;
            }

            .print-btn {
                display: none !important;
            }
        }
    </style>

    <div class="invoice-page">
        <section class="invoice-section">
            <h2 class="invoice-section-title">
                Admin Infos
                <button type="button" class="print-btn" onclick="window.print()">Drucken</button>
            </h2>

            <div class="invoice-grid">
                <div>
                    <p class="label">Kaeufer</p>
                    <p>
                        {{ trim($buyerFirstName . ' ' . $buyerLastName) }}<br>
                        {{ $boost->user_info->street ?? $boost->user?->street ?? '-' }} {{ $boost->user_info->house_no ?? $boost->user?->house_no ?? '' }}<br>
                        {{ $boost->user_info->zip ?? $boost->user?->zip ?? '-' }} {{ $boost->user_info->federal_state ?? $boost->user?->federal_state ?? '' }}<br>
                        {{ $boost->user_info->email ?? $boost->user?->email ?? '-' }}
                    </p>

                    @if (!empty($boost->user_info->vat_number ?? null))
                        <p>Steuernummer: {{ $boost->user_info->vat_number }}</p>
                    @endif
                </div>

                <div>
                    <p class="label">Anbieterinformation</p>
                    <p>
                        Frau Kruner<br>
                        Inh. Frau Kathleen Krueger<br>
                        Schoenhauser Allee 163<br>
                        10435 Berlin
                    </p>
                    <p>USt.-Ident.-Nr.: DE419009695</p>

                    <p>
                        Rechnungs-Nr:
                        @if ($useOldFormat)
                            FKB{{ $payment->payment_trnx_id }}
                        @else
                            PFK-{{ $boost->created_at->format('Y') }}-{{ $boost->id }}
                        @endif
                        <br>
                        Rechnungs-Datum: {{ $boost->created_at->format('d.m.Y') }}
                    </p>
                </div>
            </div>

            <div class="invoice-table-wrap">
                <p class="label">Details</p>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Produktname</th>
                            <th>Basispreis</th>
                            @if ($tax)
                                <th>MwSt</th>
                            @endif
                            <th>Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $boost->boostable?->name ?? $boost->boostable?->title ?? '-' }}</td>
                            <td>{{ $fmt($boost->base_price) }}</td>
                            @if ($tax)
                                <td>{{ $fmt($tax) }}</td>
                            @endif
                            <td>{{ $fmt($boost->price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if (!$tax)
                <p class="muted-note">Gemaess § 19 UStG enthaelt der o.g. Rechnungsbetrag keine Umsatzsteuer.</p>
            @endif
        </section>
    </div>
</x-filament-panels::page>
