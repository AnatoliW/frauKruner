<x-front_app>
	@section('description',$post->meta_description)
	@section('title',$post->title)
	@push('css')
	<style>
		.news-single-content a::before {
			background-color: none !important;
			height: 0 !important;
		}
	</style>
	@endpush
	@section('content')

	@php
	$nextPost = App\Models\Post::where('id', '>', $post->id)->where('status', 'PUBLISHED')->first();
	$previousPost = App\Models\Post::where('id', '<', $post->id)->where('status', 'PUBLISHED')->first();
	@endphp
	<main>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe data-name="google-tag-manager" data-src="https://www.googletagmanager.com/ns.html?id=GTM-WJ2JJ8L" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->

		<section class="bg-primary">
			<div class="container-xxl">
				<div class="row">
					<div class="col-12 col-md-6 p-0">
						<img data-src="{{ media_url($post->image) }}" class="lazy img-fluid" alt="{{ $post->title }}">
					</div>
					<div class="col-12 col-md-6 single-news-headline">
						<p>{{ $post->category?->name }}</p>
						<h1>{{ $post->title }} </h1>
					</div>
				</div>
			</div>
		</section>

		<section class="container-xxl news-single-content" style="max-width:600px;">

			{!! $post->body !!}
			<div class="navigation-between-the-posts">
				@if($previousPost)
				<a href="{{ route('posts.previous', $post->slug) }}" class="btn-article article-prev" style="text-decoration:none">
					<img data-src="{{asset('assets/img/icons/previous-arrow-news.svg')}}" class="lazy" alt="Vorheriger Artikel">
					<span>Vorhergehender Beitrag</span>
				</a>
				@else
				<a href="" class="btn-article article-prev" style="text-decoration:none;pointer-events: none; opacity: 0.6;">
					<img data-src="{{asset('assets/img/icons/previous-arrow-news.svg')}}" class="lazy" alt="Vorheriger Artikel">
					<span>Vorhergehender Beitrag</span>
				</a>
				@endif
			
				@if($nextPost)
				<a href="{{ route('posts.next', $post->slug) }}" class="btn-article article-next">
					<span>Nächster Beitrag</span>
					<img data-src="{{asset('assets/img/icons/arrow-next-news.svg')}}" class="lazy" alt="Nächster Artikel">
				</a>
				@else
				<a href="" class="btn-article article-next " style="  pointer-events: none; opacity: 0.6;">
					<span>Nächster Beitrag</span>
					<img data-src="{{asset('assets/img/icons/arrow-next-news.svg')}}" class="lazy" alt="Nächster Artikel">
				</a>
				@endif
			</div>
		</section>

	</main>
</x-front_app>
