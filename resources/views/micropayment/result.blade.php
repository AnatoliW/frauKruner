@php
    $texts = [
        'completed' => [
            'headline' => 'Vielen Dank<br> für deinen Einkauf!',
            'body' => 'Deine Zahlung ist bei uns eingegangen. Die Bestellbestätigung ist per E-Mail unterwegs.',
        ],
        'pending' => [
            'headline' => 'Danke!<br> Wir warten auf deine Zahlung.',
            'body' => 'Deine Bank hat die Überweisung angestoßen, sie ist aber noch nicht bei uns gutgeschrieben. Sobald das Geld da ist, bekommst du die Bestellbestätigung per E-Mail. Bitte beachte: Dein Produkt ist bis dahin nicht reserviert.',
        ],
        'failed' => [
            'headline' => 'Die Zahlung hat nicht geklappt',
            'body' => 'Es ist kein Geld abgebucht worden. Du kannst die Bestellung mit einer anderen Zahlungsart erneut abschließen.',
        ],
        'mismatch' => [
            'headline' => 'Wir prüfen deine Zahlung',
            'body' => 'Der gemeldete Betrag passt nicht zu deiner Bestellung. Wir schauen uns das an und melden uns per E-Mail bei dir. Bitte zahle nicht noch einmal.',
        ],
        'fallback' => [
            'headline' => 'Danke für deine Bestellung',
            'body' => 'Wir haben eine Rückmeldung zu deiner Zahlung erhalten und prüfen sie. Sobald der Status feststeht, melden wir uns per E-Mail bei dir.',
        ],
    ];

    $text = $texts[$state] ?? $texts['fallback'];
@endphp

@extends('layouts.app')
@section('title', 'Zahlung')
@section('content')

    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center">
                <div class="thanks">
                    <h2 class="mt-3 {{ $state === 'completed' ? 'text-primary' : '' }} mb-4">{!! $text['headline'] !!}</h2>
                </div>
            </div>
            <div class="col-12 d-flex flex-column align-items-center">
                <p class="text-center" style="max-width:600px;">{{ $text['body'] }}</p>

                @if ($subject)
                    <table class="table text-secondary" style="max-width:600px;">
                        <tbody>
                            <tr>
                                <td scope="row">Vorgang:</td>
                                <th>{{ $subject->reference() }}</th>
                            </tr>
                            <tr>
                                <td scope="row">Betrag:</td>
                                <th>{{ $subject->amountForHumans() }}</th>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if (in_array($state, ['failed', 'mismatch'], true) && $subject)
                    <a class="btn btn-primary mt-3" href="{{ $subject->backUrl() }}">Zurück zur Zahlungsart</a>
                @else
                    <a class="btn btn-primary mt-3" href="{{ route('shop') }}">Weiter einkaufen</a>
                @endif
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
