@props([
    // 'voll'    = der ganze Beleg ist storniert
    // 'teilweise' = nur einzelne Positionen des Belegs
    'scope' => 'voll',
    'label' => null,
])

{{--
    Storno-Markierung für die Belege im Adminbereich.

    Gegenstück zum roten Balken der Kundenansicht (.card-body.storniert::before
    in style.css). Als eigenes Element und nicht als ::before, damit es auch im
    Ausdruck steht und einen Text tragen kann.
--}}
<div class="invoice-document__cancelled invoice-document__cancelled--{{ $scope }}">
    {{ $label ?? ($scope === 'voll' ? 'Storniert' : 'Teilweise storniert') }}
</div>
