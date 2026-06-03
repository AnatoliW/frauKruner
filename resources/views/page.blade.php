


@push('css')
        <style>
            .collapse_faq__sidebar {

                margin-left: 0 !important;
            }
        </style>
    @endpush

<x-front_app>
@section('title',$page->title)
@section('description',$page->meta_description)
	<div class="pages bg-white">
		{{-- <div class="container">
			<h3 class="h3 mb-1 heading">{{$page->title}} </h3>
			<hr />
			<div class="row">
				<div class="col-md-12">
				   {!! $page->body !!}
				</div>
			</div>
		</div>
	</div> --}}

    <main>
            {!! $page->body !!}
    </main>
</x-front_app>



