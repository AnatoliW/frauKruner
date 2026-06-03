<x-front_app>
  @section('title','Frau Kruner´s - Geheimnisse, Tipps und Neuigkeiten')
  @section('description','Playboy, Orion, YAM FM und vieles mehr. Erfahre wo Frau Kruner zu Gast war, wer der neue Shop der Woche geworden ist, lass dich von Erfahrungsberichten faszinieren und von Artikeln unterhalten. In den Podcasts erhältst du exklusives Hintergrundwissen zum Thema getragene Unterwäsche und mehr.
FrauKruner.de - die Nr. 1 für getragene Unterwäsche, Socken und Co.')
  <main>

  </main>

  <noscript><iframe data-name="google-tag-manager" data-src="https://www.googletagmanager.com/ns.html?id=GTM-WJ2JJ8L" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <section class="container-xxl">
    <div class="blog-intro">
      <h1><span class="text-primary">Frau Kruner´s</span> <br>
        Geheimnisse, Tipps und Neuigkeiten. </h1>
    </div>
  </section>
 
  <section class="container-xxl">


			<!--Category News Slider-->
			<div class="container-categorys-desktop-news-slider">
				<div class="categorys-desktop-news-slider">
					<div class="categorys-desktop-news-slider__slide {{request()->has('category')==false ? 'current-menu-item' :''}}">
            <a href="{{route('blog')}}">Alle</a>
          </div>

          @foreach ($categories as $category)
          <div class="categorys-desktop-news-slider__slide {{ request('category')==$category->slug ? 'current-menu-item' : ''}}">
            <a href="{{route('blog',['category'=>$category->slug])}}">{{ $category->name }}</a>
          </div>
          @endforeach

        </div>

        <button class="button-news-cat-slide">
					<img src="https://fraukruner.de/assets/img/icons/ios-arrow-slide-news.svg" alt=">">
				</button>
      </div>

    </div>
    <!-- Article List Archive -->

    <div class="article-list-archive">
      <x-posts :posts="$posts" />


    </div>
  </section>

  </main>
</x-front_app>