<x-dashboard type='seller' title="Erfahrungen" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Erfahrungen' => route('seller.reviews'),
]">

    <div class="card-fields-shopping-cart mt-5">

        <!-- Message Item-->

            @forelse ($reviews as  $review)
            <div class="card-item-messages__main-info mb-3">
                    <div class="d-flex justify-content-between flex-column flex-md-row w-100">
                        <div>
                            <b class="pt-1">{{ $review->user->username[0] }}.</b>
                            <p>{{$review->review}}</p>
                        </div>

                        <a class="float-left no-before" href="#"><strong>{{$review->name}}</strong></a>
                        <input name="rating" type="number" value="{{$review->rating}}" class="rating published_rating" data-size="xs">
                    </div>
            <hr>

            </div>

            @empty
            <h5>Keine Rezensionen gefunden</h5>

            @endforelse



        <!-- Message Item-->





    </div>
    @push('scripts')
    <script src="{{asset('js/custom/star-rating.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        $("#product_rating").rating({
            showCaption: true
        });
        $(".published_rating").rating({
   showCaption: false,
   readonly:true,
});


    </script>
    @endpush
</x-dashboard>
