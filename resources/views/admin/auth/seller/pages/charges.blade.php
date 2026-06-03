<x-dashboard type='seller' title="Rechnungen" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Rechnungen' => route('seller.charges'),
]">
    <div class="card-fields-shopping-cart" >
        @forelse ($boosts as $boost)
            <div class="card-item-messages">
                <div class="card-item-messages__main-info">
                    <div class="col-prod-text">
                        <div class="col-prod-text__prod-summary">
                            <h6 class="text-primary">{{ optional($boost->boostable)->name ?? '' }}</h6>
                            <p class="m-0">{{ optional($boost->package)->name ?? '' }}</p>
                            <p class="m-0 text-muted">{{ optional($boost->created_at)->format('d.M.Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="col-prod-price">
                        @if($boost->payments->where('status', 'PAID')->count() > 0)
                            <div class="col-prod-profile-sells-buttons">
                                <a class="btn btn-secondary" href="{{ route('seller.charges.invoice', $boost) }}">Rechnung</a>
                            </div>
                            <a class="btn btn-success m-2 btn-sm">{{ optional($boost->payments->first())->statusTranslated }}</a>
                        @else
                            <a class="btn btn-warning m-2 btn-sm" href="{{ route('seller.payment', $boost->payments->first()) }}">{{ optional($boost->payments->first())->statusTranslated }}</a>
                        @endif
                        <span class="col-prod-price__price">{{ Shop::price($boost->price ?? 0) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p>Keine Rechnungen vorhanden.</p>
        @endforelse
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $(".product-boost").click(function() {
                var url="{{ route('seller.boost.store') }}";
                var route= url + '/' + $(this).data('id');
                $("#boost-form").attr("action", route);
            });
        });
    </script>
    @endpush
</x-dashboard>
