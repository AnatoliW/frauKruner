{{--
    Gutschein entfernen. Als Formular und nicht als Link, weil der Aufruf den
    Warenkorb ändert: Ein <a href> hätte jedem Prefetch und jedem Crawler
    gereicht, um der Kundin den Rabatt wegzunehmen.

    An dieser Stelle steht nur der Button. Das zugehörige <form> wird ans Ende
    der Seite geschoben und über das form-Attribut verknüpft, weil der Rabatt in
    checkout.blade.php mitten im Bestellformular angezeigt wird:

    Ein <form> im <form> ist nicht erlaubt. Der Browser verwirft das innere
    Start-Tag, dessen </form> beendet aber das äußere Formular. Alles danach –
    das Feld payment_id und der Button "zur Zahlung" – hatte dadurch kein
    Formular mehr und der Bestellvorgang ließ sich nicht mehr abschicken.
    Umgekehrt gehörte dieser Löschen-Button dann zum Bestellformular und hätte
    die Bestellung ausgelöst statt den Gutschein zu entfernen.

    Sieht weiter aus wie der bisherige Link, damit sich am Layout nichts ändert.
--}}
<button type="submit" form="coupon-remove-form"
    style="background:none;border:0;padding:0;font:inherit;color:inherit;text-decoration:underline;cursor:pointer;">(
    Löschen )</button>

{{-- Der Stack 'js' wird im Footer ausgegeben, also außerhalb jedes Formulars. --}}
@once
    @push('js')
        <form id="coupon-remove-form" action="{{ route('coupon.destroy') }}" method="post" hidden>
            @csrf
        </form>
    @endpush
@endonce
