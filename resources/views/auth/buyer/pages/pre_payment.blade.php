@php
    $price = $order->total;
    $date = $order->created_at;
    $orderId = $order->id;
@endphp

<x-dashboard type='buyer' title="Vorkasse" :bread="[
    'Startseite' => route('buyer.dashboard'),
]">
    <div class="d-flex flex-column align-items-start justify-content-start">
        {{--
        <form action="{{ route('buyer.pre.payment.prove', $order) }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <label for="prove" class="visible mt-3">
                        <details data-popover="up">
                            <summary>?</summary>
                            <div class="popoverBody">
                                Bitte lade hier ein Foto der Vorderseite deines Personalausweises hoch. Dies dient
                                deiner
                                Altersprüfung. Bei Fragen lies dir die <a href="/page/nutzungsbedingungen">Richtlinien
                                    durch</a>.
                            </div>
                        </details>
                        Vorderseite deines Personalausweises:
                    </label>
                    <input type="file" id="prove" class="@error('meta.payment_prove') is-invalid @enderror"
                        name="meta[payment_prove]" accept="image/*">

                    @error('meta.payment_prove')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @if ($order->payment_prove)
                    <div class="col-md-6">
                        <img src="{{ media_url($order->payment_prove) }}" alt="" height="50">
                    </div>
                @endif
            </div>
            <button class="btn btn-secondary">Save</button>
        </form>
        --}}

        <p >
            Bitte überweise: <b class="h6">{{$order->total}} €</b><br>
            auf das folgende Konto unter Angabe des Verwendungszwecks:<br>
            </p>
            <table class="table text-secondary" style="max-width:600px;">
            <tbody>
                <tr>
                    <td scope="row">Kontoinhaberin:</td>
                    <th>Kathleen Krüger</th>
                </tr>
                <tr>
                    <td scope="row">Parent ID:</td>
                    <th>{{$order->parent_id}}</th>
                </tr>
                <tr>
                    <td scope="row">IBAN:</td>
                    <th>DE65 1101 0101 5821 8303 92</th>
                </tr>
                <!-- <tr>
                    <td scope="row">BIC:</td>
                    <th>SOBKDEB2XXX</th>
                </tr> -->
                <tr>
                    <td scope="row">Verwendungszweck:<br><small class="text-primary">(zwingend erforderlich)</small></td>
                    <th>FK{{$order->created_at->year}}-{{$order->id}}</th>
                </tr>
                <tr>
                    <td scope="row">Betrag:</td>
                    <th>{{$order->total}} €</th>
                </tr>

            </tbody>
            </table>
            <p>
                *Bitte zahle so schnell wie möglich, da dein gewünschtes Produkt nicht reserviert ist.<br> Sollte dies nach deiner Überweisung und vor dem Zahlungseingang anderweitig verkauft werden,<br> 
            erstatten wir dir dein Geld zurück.
        </p>

        @if ($order->payment_status == 0 && $order->status !== 3)
            <a class="btn btn-primary" target="_blank" href="{{ route('payment', $order->parent_id) }}">Andere Bezahlmethode wählen</a>
        @endif
    </div>


</x-dashboard>

