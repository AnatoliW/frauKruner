<x-dashboard type='buyer' title="Meine Nachrichten" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Meine Verkäufe' => route('buyer.orders'),
    'Meine Nachrichten' => route('buyer.news'),
]">
    <div class="card-fields-shopping-cart">

        <!-- Message Item-->

        @forelse($notifications as $key => $notification)

        <!-- Message Item-->
        <div class="card-item-messages">
            <div class="card-item-messages__main-info">
                <div class="col-prod-image">
                    @if($notification->image=='notifications/icon.png')
                    <img data-src="{{asset('assets/img/icons/mitteilung-kruner.svg')}}" class="lazy img-fluid"  style="object-fit:contain;" alt="{{ $notification->title }}">

                    @else
                    <img data-src="{{  media_url($notification->product->image) }}" class="lazy img-fluid" style="object-fit:contain;" alt="{{ $notification->title }}">
                    @endif
                </div>

                <div class="col-prod-text pt-3">
                    <div class="col-prod-text__prod-summary">
                        @if($notification->title )
                        <h6 class="text-primary"> <span> {{ $notification->title }}</span></h6>
                        @else
                        <h6 class="text-primary"> <span> {{$notification->product ? $notification->product->category->name :'' }}</span></h6>
                        @endif
                        @if($notification->description )
                        <p>{!!$notification->description!!}</p>
                        @else
                        <h6 class="text-primary"> <span> {{$notification->product ? $notification->product->name  :'' }}</span></h6>
                        @endif



                    </div>

                </div>
                <div class="col-prod-price">

                    <span class="col-prod-price__price">{{ $notification->price ? Shop::price($notification->price): ''  }}</span>
                </div>
            </div>

            <div class="col-prod-addons">
                <div class="col-prod-addons__placeholder"></div>

            </div>

        </div>
        @empty
        <h5 class="mt-5">Keine Nachrichten gefunden</h5>
        <!-- Message Item From Plattform-->
        @endforelse

        <!-- Message Item From Plattform-->






    </div>

</x-dashboard>

