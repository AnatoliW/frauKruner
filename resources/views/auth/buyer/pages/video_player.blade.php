<x-dashboard type='buyer' title="Video" :bread="[
    'Startseite' => route('buyer.dashboard'),
    'Profil' => route('buyer.dashboard'),
    'Nutzerdaten' => route('buyer.user.data'),
    'Verifizierung' => route('buyer.data.verify'),
]">

<link
href="https://unpkg.com/video.js@7/dist/video-js.min.css"
rel="stylesheet"
/>
<link
  href="https://unpkg.com/@videojs/themes@1/dist/city/index.css"
  rel="stylesheet"
/>
<style>
    .vjs-poster img{
        height: 100%;
        width: 100%;
    }
</style>
    <video height="500" class="video-js vjs-theme-city w-100" id="my-video"  controls preload="auto"  poster="{{asset('assets/img/verkaeferin-werden/verkaeferin-werden.webp')}}"
        data-setup="{}">
        <source src="{{ Storage::url($order->video) }}" type="video/mp4" />
        <source src="{{ Storage::url($order->video) }}" type="video/webm" />
        <p class="vjs-no-js">
            To view this video please enable JavaScript, and consider upgrading to a
            web browser that
            <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
        </p>
    </video>

    <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>

</x-dashboard>
