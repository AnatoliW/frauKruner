@props(['positions'])

@php
    $hasVat = $positions->contains(fn ($position) => (int) ($position->seller_info->is_pay_vat ?? 0) === 1);
    $activePositions = $positions->reject(fn ($position) => (int) ($position->status ?? 0) === 3);
    $gross = (float) $activePositions->sum('vendor_total');
    $netFor = static fn ($position): float => (int) ($position->seller_info->is_pay_vat ?? 0) === 1
        ? round((float) $position->vendor_total / 1.19, 2)
        : (float) $position->vendor_total;
    $net = (float) $activePositions->sum($netFor);
    $format = static fn ($value): string => sprintf('%.2f €', (float) ($value ?? 0));
    $selection = static fn ($values): string => collect((array) $values)->map(
        fn ($value) => strstr((string) $value, '-', true) ?: (string) $value
    )->implode(', ') ?: '-';
@endphp

<table {{ $attributes }}>
    <thead>
        <tr>
            <th>Produktname</th>
            <th>Versandkosten</th>
            <th>Veredelungen</th>
            <th>Zusatzoptionen</th>
            <th>Tragedauer</th>
            <th>Basispreis</th>
            @if ($hasVat)
                <th>Netto</th>
                <th>MwSt. (19 %)</th>
            @endif
            <th>Gesamt</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($positions as $position)
            <tr @if ((int) ($position->status ?? 0) === 3) style="text-decoration:line-through" @endif>
                <td>
                    {{ $position->product_name ?? $position->product?->name ?? '-' }}
                    @if ((int) ($position->status ?? 0) === 3)
                        (storniert)
                    @endif
                </td>
                <td>{{ $format($position->shipping_cost) }}</td>
                <td>{{ $selection($position->finishings) }}</td>
                <td>{{ $selection($position->addition) }}</td>
                <td>{{ $selection($position->wearing_time) }}</td>
                <td>{{ $format($position->subtotal) }}</td>
                @if ($hasVat)
                    <td>{{ $format($netFor($position)) }}</td>
                    <td>{{ $format((float) $position->vendor_total - $netFor($position)) }}</td>
                @endif
                <td>{{ $format($position->vendor_total) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right"><strong>Gesamtbetrag</strong></td>
            @if ($hasVat)
                <td><strong>{{ $format($net) }}</strong></td>
                <td><strong>{{ $format($gross - $net) }}</strong></td>
            @endif
            <td><strong>{{ $format($gross) }}</strong></td>
        </tr>
    </tfoot>
</table>
