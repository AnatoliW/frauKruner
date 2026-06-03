<x-dashboard type='seller' title="Meine Auszahlungen" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Meine Auszahlungen' => route('seller.payments'),
]">

    <div class="card-fields-shopping-cart" >
        @foreach($payments as $payment)
        <!-- Message Item-->
        <div class="card-item-messages">
            <div class="card-item-messages__main-info">
                <div class="col-prod-image">
                    <img data-src="{{ media_url($payment->product->image) }}" class="lazy img-fluid" alt="">
                </div>

                <div class="col-prod-text">
                    <div class="col-prod-text__prod-summary">
                        <h6 class="text-primary">{{$payment->product? $payment->product->category->name : $payment->title }}</h6>
                        <p>{{$payment->product->name}}</p>
                    </div>

                </div>
                <div class="col-prod-price">
                    <form action="{{route('seller.payouts.request',$payment)}}" method="post">
                        @csrf
                    @if($payment->payouts_rerquest==0)
                    <!-- <button OnClick='return (confirm("Bist du sicher, dass du eine Auszahlung anfragen möchtest?"));' type="submit" class="btn btn-secondary">
                        Auszahlung anfragen
                    </button> -->
                    @elseif ($payment->payouts_status==1)
                    <button  class="btn btn-secondary" disabled>
                        Auszahlung erfolgreich
                    </button>
                    @else
                    <!-- <button  class="btn btn-secondary" disabled>
                        Versendet
                    </button> -->
                    @endif
                    </form>
              
                    <span class="col-prod-price__price">{{ Shop::price($payment->vendor_total) }}</span>
                </div>
            </div>

            <div class="col-prod-addons">
                <div class="col-prod-addons__placeholder"></div>

            </div>

        </div>
        @endforeach
        <!-- Message Item-->






    </div>

</x-dashboard>

