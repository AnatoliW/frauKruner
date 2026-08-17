{{--
    Vorschau statt Weiterleitung, solange Zugangsdaten fehlen.

    Ohne AccessKey wäre die Signatur wertlos, ohne Projektkürzel ließe sich die
    Buchung keinem Projekt zuordnen – Micropayment würde die Anfrage in beiden
    Fällen mit einer eigenen Fehlerseite abweisen. Diese Seite zeigt stattdessen,
    was der Shop erzeugt hätte.

    Sobald alle Einträge gesetzt sind, wird direkt weitergeleitet und diese Seite
    erscheint nicht mehr.
--}}

@extends('layouts.app')
@section('title', 'Micropayment – Vorschau')
@section('content')

    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h2 class="mt-3 mb-2">Online-Überweisung – Vorschau</h2>
                <p class="text-secondary">
                    Es fehlen noch Zugangsdaten. Statt zum Zahlungsfenster weiterzuleiten, zeigt der Shop
                    hier, welcher Aufruf erzeugt worden wäre.
                </p>

                <h5 class="mt-4">Diese Einträge fehlen in der <code>.env</code></h5>
                <table class="table text-secondary" style="max-width:700px;">
                    <tbody>
                        @foreach ($missing as $variable => $description)
                            <tr>
                                <td><code>{{ $variable }}</code></td>
                                <th>{{ $description }}</th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-secondary small">
                    Beide Werte stehen im Micropayment Control-Center. Sobald sie eingetragen sind,
                    entfällt diese Seite – ohne Codeänderung. Wenn die Änderung nicht greift,
                    <code>php artisan config:clear</code> ausführen.
                </p>

                <h5 class="mt-4">Vorgang</h5>
                <table class="table text-secondary" style="max-width:700px;">
                    <tbody>
                        <tr>
                            <td>Referenz</td>
                            <th>{{ $subject->reference() }}</th>
                        </tr>
                        <tr>
                            <td>Betrag</td>
                            <th>{{ $subject->amountForHumans() }} ({{ $securedParameters['amount'] }} Cent)</th>
                        </tr>
                    </tbody>
                </table>

                <h5 class="mt-4">Signierte Parameter</h5>
                <table class="table text-secondary" style="max-width:700px;">
                    <tbody>
                        @foreach ($securedParameters as $name => $value)
                            <tr>
                                <td>{{ $name }}</td>
                                <th style="word-break:break-all;">{{ $value === '' ? '—' : $value }}</th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="mt-4">Nicht signierte Parameter</h5>
                <table class="table text-secondary" style="max-width:700px;">
                    <tbody>
                        @foreach ($unsecuredParameters as $name => $value)
                            <tr>
                                <td>{{ $name }}</td>
                                <th>{{ $value }}</th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="mt-4">Erzeugte Zahlungsfenster-URL</h5>
                <pre style="white-space:pre-wrap; word-break:break-all; background:#f6f6f6; padding:1rem; border-radius:.25rem;">{{ $paymentUrl }}</pre>

                <h5 class="mt-4">Benachrichtigungs-URL für das Control-Center</h5>
                <pre style="white-space:pre-wrap; word-break:break-all; background:#f6f6f6; padding:1rem; border-radius:.25rem;">{{ $notificationUrl }}</pre>
                <p class="text-secondary small">
                    Diese Adresse im Micropayment Control-Center bei der Zahlart als Benachrichtigungs-URL
                    hinterlegen. Sie muss von außen erreichbar sein – lokal also z.&nbsp;B. über einen Tunnel.
                </p>

                <a class="btn btn-primary mt-3" href="{{ $subject->backUrl() }}">Zurück zur Zahlungsart</a>
            </div>
        </div>
    </div>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
