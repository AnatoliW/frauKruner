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
                      <h6 class="card-title mb-3">Zusammenfassung der Bestellungen</h6>
                      <p>{{$payment->payable->package->name}}</p>
                      <div class="d-flex justify-content-between mb-1 small">
                        <span>Zwischensumme</span> <span>{{Shop::price($payment->payable->price)}}</span>
                      </div>
                   
                      {{-- <div class="d-flex justify-content-between mb-1 small">
                        <span>Coupon (Code: NEWYEAR)</span> <span class="text-danger">-$10.00</span>
                      </div> --}}
                      <hr>
                      <div class="d-flex justify-content-between mb-4 small">
                        <span>Gesamtsumme</span> <strong class="text-dark">{{Shop::price($payment->payable->price)}}</strong>
                      </div>
                      
                     @if($payment->status=="PAID")
                      <button disabled class="btn btn-boost w-100 mt-2">Bezahlt</button>
                      @else
                      <a style="background-color: #FFCE00" href="{{route('seller.payment.process',$payment->id)}}" class="btn btn-primary w-100 mt-2 text-white border-0 py-0">
                        <img height="50px" src="{{asset('images/paypal.png')}}" alt="">
                       </a>
                      @endif
                    </div>
                  </div>
                
              </div>
             
            </div>
 

    @push('scripts')
      

   
    @endpush
</x-dashboard>
