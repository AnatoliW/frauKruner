<x-dashboard type='seller' title="" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard')
]">
    <main class="profile-content panel">
            <h1 class="h3 mb-5">Bezahlung</h1>
            <div class="row">
              <!-- Left -->
              <div class="col-lg-9">
                  
                  {{-- <div class="accordion-item">
                     <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQLYmR2q4q9OeWlnLrltdW3ekbu2GsvqcUorBfsIZO5uQ&s" alt="" class="w-100" style="height: 100px">
                
                  </div> --}}
                  <div class="card position-sticky top-0">
                    <div class="p-3 bg-light bg-opacity-10">
                      @php
                        // Maßgeblich ist die Zahlung, nicht der Boost: BoostController rechnet
                        // die MwSt. für den Payment direkt aus, während boost.tax über
                        // Shop::tax() läuft – und das liefert ab 50 € Paketpreis 0 zurück.
                        // Der Payment trägt außerdem genau den Betrag, der abgebucht wird.
                        $brutto = (float) $payment->amount;
                        $mwst = (float) $payment->tax;
                        $netto = $brutto - $mwst;

                        // Satz aus dem Datensatz ableiten statt aus der aktuellen Einstellung:
                        // Ältere Zahlungen wurden ggf. mit einem anderen Satz gerechnet.
                        $satz = $netto > 0 ? round($mwst / $netto * 100, 1) : 0;
                      @endphp

                      <h6 class="card-title mb-3">Zusammenfassung der Bestellungen</h6>
                      <p>{{$payment->payable->package->name}}</p>
                      <div class="d-flex justify-content-between mb-1 small">
                        <span>Zwischensumme (netto)</span> <span>{{Shop::price($netto)}}</span>
                      </div>
                      <div class="d-flex justify-content-between mb-1 small">
                        <span>zzgl. MwSt.@if($satz > 0) ({{ rtrim(rtrim(number_format($satz, 1, ',', '.'), '0'), ',') }} %)@endif</span>
                        <span>{{Shop::price($mwst)}}</span>
                      </div>

                      {{-- <div class="d-flex justify-content-between mb-1 small">
                        <span>Coupon (Code: NEWYEAR)</span> <span class="text-danger">-$10.00</span>
                      </div> --}}
                      <hr>
                      <div class="d-flex justify-content-between mb-4 small">
                        <span>Gesamtsumme</span> <strong class="text-dark">{{Shop::price($brutto)}}</strong>
                      </div>
                      
                     @if($payment->status=="PAID")
                      <button disabled class="btn btn-boost w-100 mt-2">Bezahlt</button>
                      @else
                      {{--
                        PayPal ist abgeschaltet, weil die Anbindung derzeit nicht funktioniert.
                        Hervorhebungen werden ausschließlich per Online-Überweisung bezahlt.

                        Zum Reaktivieren zusätzlich einkommentieren:
                        - routes/seller.php: payment.process und payment.success
                        - Seller\PaymentController: paymentProcess() und success()

                      <a style="background-color: #FFCE00" href="{{route('seller.payment.process',$payment->id)}}" class="btn btn-primary w-100 mt-2 text-white border-0 py-0">
                        <img height="50px" src="{{asset('images/paypal.png')}}" alt="">
                       </a>
                      --}}

                       @if (\App\Payment\MicropaymentGateway::isEnabled())
                        {{-- Signiert, weil die Zahlungsfenster-URL Name und E-Mail-Adresse
                             enthält und die Zahlungsnummern fortlaufend sind. --}}
                        <a href="{{ URL::temporarySignedRoute('payment.micropayment.boost', now()->addMinutes(30), ['payment' => $payment->id]) }}"
                           class="btn btn-primary w-100 mt-2">
                          Online-Überweisung
                        </a>
                        <p class="small text-secondary mt-2 mb-0">
                          Du wirst zu deiner Bank weitergeleitet und bestätigst die Überweisung dort im
                          gewohnten Online-Banking. Die Hervorhebung startet, sobald die Zahlung bei uns
                          eingegangen ist.
                        </p>
                       @else
                        {{-- Ohne Online-Überweisung gäbe es sonst gar keine Zahlungsmöglichkeit
                             und die Seite bliebe wortlos leer. --}}
                        <p class="small text-secondary mt-2 mb-0">
                          Zurzeit steht keine Zahlungsart zur Verfügung. Bitte versuche es später
                          noch einmal oder wende dich an den Support.
                        </p>
                       @endif
                      @endif
                    </div>
                  </div>
                
              </div>
             
            </div>
 

    @push('scripts')
      

   
    @endpush
</x-dashboard>
