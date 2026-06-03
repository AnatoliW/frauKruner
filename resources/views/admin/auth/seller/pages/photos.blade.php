<x-dashboard type='seller' title="Foto & Video" :bread="[
    'Startseite' => route('home'),
    'Profil' => route('seller.dashboard'),
    'Foto & Video' => route('seller.photos',$order),
]">
<style>
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 1);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;

        }

        #loadingOverlay img {
            max-width: 100px;

        }


        .blur-content {
            filter: blur(4px);
            pointer-events: none;
        }
    </style>

    <div id="loadingOverlay" style="display: none">
        <div class="d-flex justify-content-center flex-column align-items-center">
            <img src="{{ asset('assets/img/icons/lade-anim-farbe.gif') }}" alt="Datei wird hochgeladen">
            Datei wird hochgeladen...
        </div>
    </div>

    <div class="card-fields-shopping-cart">
        @if(!$order->orderimages->isEmpty())
        <h4>Foto(s)</h4>
        @foreach ($order->orderimages as $image)

        <!-- Product Item-->
        <div class="card-item">
            <div class="card-item__main-info">
                <div class="col-prod-image">
                    <img data-src="{{ media_url($image->image) }}" class="lazy img-fluid" alt="">
                </div>

                <div class="col-prod-text">


                </div>

                <div class="col-prod-price">
                    <!-- Link Only for Testing-->
                    <div class="d-flex">
                        <!-- <a href="">
                            <img data-src="{{ asset('assets/img/icons/edit-icon.svg') }}" alt="Edit image" class="lazy col-prod-price__erase">
                        </a> -->
                        <form action="{{route('seller.photo.delete',$image)}}" method="post">
                            @csrf
                            <button class="pt-1" type="submit" onclick="return confirm('Sicher, dass du das Foto löschen möchtest?')">
                                <img data-src="{{ asset('assets/img/icons/close-icon.svg') }}" alt="Foto löschen" class="lazy col-prod-price__erase">
                            </button>
                        </form>
                    </div>



                </div>
            </div>

            <div class="col-prod-addons">
                <div class="col-prod-addons__placeholder"></div>

            </div>

        </div>
        <!-- Product Item-->
        @endforeach
        @endif


        @if($order->video==!null)
        <h4 class="mt-4">Video(s)</h4>
        <div class="card-item">
            <div class="card-item__main-info">
                <div class="col-prod-image">
                    <video src="{{ media_url( $order->video) }}" controls width="300"></video>
                </div>

                <div class="col-prod-text">


                </div>

                <div class="col-prod-price">
                    <!-- Link Only for Testing-->
                    <div class="d-flex">
                        <!-- <a href="">
                            <img data-src="{{ asset('assets/img/icons/edit-icon.svg') }}" alt="Edit image" class="lazy col-prod-price__erase">
                        </a> -->
                       
                            <button class="pt-1" data-bs-toggle="modal" data-bs-target="#video" data-bs-whatever="{{$order->id}}">
                            <img data-src="{{ asset('assets/img/icons/edit-icon.svg') }}" alt="Video löschen" class="lazy col-prod-price__erase">
                            </button>
                 
                    </div>



                </div>
            </div>

            <div class="col-prod-addons">
                <div class="col-prod-addons__placeholder"></div>

            </div>

        </div>
        @endif


    </div>

    <div class="modal fade" id="video" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">
                            Video hochladen
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('seller.video.upload')}}" method="post" enctype="multipart/form-data" id="videoForm">
                        @csrf
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="col-12 ">
                                    <h5 class="small">
                                        <details data-popover="up">
                                            <summary>?</summary>
                                            <div class="popoverBody">
                                                Das Video ist für den Käufer des Produktes sichtbar. Weitere Infos findest du in unseren <a href="/page/nutzungsbedingungen" target="_blank">Nutzungsbedingungen</a>.
                                            </div>
                                        </details> Video hochladen (max. 2GB)
                                    </h5>
                                    <div class="border-profile">
                                    <input type="file" name="video" id="videoInput" style="border:1px solid #B2B2B2;" >
                                    <input type="hidden" name="order_id" value="{{$order->id}}" id="video_order_id">
                                </div>

                                    <script>
                                
                                </script>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="video-upload-btn" class="btn btn-primary me-2">Hochladen</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>

</x-dashboard>
