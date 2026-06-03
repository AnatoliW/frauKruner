@foreach($posts as $post)
<!-- Single Products Item-->
<div class="article-list-archive__article">
	<a href="{{route('post_details',$post->slug)}}">
		<div class="article-list-archive__article__image">
			<img data-src="{{ media_url($post->image) }}" class="lazy" alt="{{ $post->title }} ">
		</div>
		<div class="article-list-archive__article__content">
			<p class="text-uppercase">{{ $post->category?->name }}</p>
			<h3>{{ $post->title }} </h3>
			<p>{{ $post->excerpt}}</p>
		</div>
	</a>
</div>
<!-- Single Products Item-->
@endforeach
