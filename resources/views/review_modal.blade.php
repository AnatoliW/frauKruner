@if($vendor && $vendor->ratings)
@if ($vendor->ratings->count() > 0)
    @foreach ($vendor->ratings as $review)
        <div class="single-review" style="border-bottom: 1px solid #B2B2B2;padding:  0; ">

            <div class="star-rating" style="flex-direction:column;align-items:start">

                <input name="rating" type="number" value="{{ $review->rating }}"
                                                class="rating published_rating" data-size="xs">
                <b class="pt-1 text-dark">{{ $review->user->username[0] }}.</b>
                <p class="text-dark">{{ $review->review }}</p>
            </div>
        </div>
    @endforeach

@endif
@endif


        <script src="{{ asset('js/custom/star-rating.js') }}" type="text/javascript"></script>
        <script type="text/javascript">
            $("#product_rating").rating({
                showCaption: true
            });
            $(".published_rating").rating({
                showCaption: false,
                readonly: true,
            });
        </script>

