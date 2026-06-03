   @push('css')
       <link href="{{ asset('assets/css/summernote.min.css') }}" rel="stylesheet">

       <link href="{{ asset('assets/css/summernote-lite.min.css') }}" rel="stylesheet">
       <style>
           .note-toolbar {
               display: none;
           }
       </style>
   @endpush

   @props(['data'])



   <form method="post" action="{{ route('info.update') }}" enctype="multipart/form-data">
       @csrf
       <div class="row">
           <div class="col-12 col-md-6 mt-4">
               <h5 class="small">
                   <details data-popover="up">
                       <summary>?</summary>
                       <div class="popoverBody">
                           Verwende hier nicht deinen echten Namen.
                       </div>
                   </details>Nutzername (Pseudonym)
               </h5>
               <input type="text" id="nutzername" name="username"
                   value="{{ auth()->user()->username ? auth()->user()->username : old(username) }}"
                   placeholder="Nutzername">
           </div>

           <div class="col-12 col-md-6 mt-4">
               <h5 class="small">
                   <details data-popover="up">
                       <summary>?</summary>
                       <div class="popoverBody">
                           Das Profilbild ist für jeden sichtbar. Achte hier auf den Jugendschutz (FSK 16) und unsere <a
                               href="/page/nutzungsbedingungen" target="_blank">Nutzungsbedingungen</a>.
                       </div>
                   </details>Profilbild
               </h5>
               <div class="border-profile">
                   <input type="file" name="profile_img" id="profilbild" style="border:1px solid #B2B2B2;">
                   @if ($data->profile_img)
                       <a href="{{ media_url($data->profile_img) }}">Bild ansehen</a>
                   @endif
               </div>
           </div>
           @if (Auth()->user()->role_id == 3)
               <div class="col-12 mt-4">
                   <h5 class="small">
                       <details data-popover="up">
                           <summary>?</summary>
                           <div class="popoverBody">
                               Dies ist kein Pflichtfeld. Du kannst etwas über dich erzählen, dich beschreiben,
                               eine Geschichte erzählen oder einfach nur deine Hobby´s nennen. Just for fun. Tipp
                               von mir: mach dich interessant.
                           </div>
                       </details>Beschreibung
                   </h5>
                   <textarea name="description" id="beschreibung">{{ $data->description ?? old('description') }}</textarea>

                   <div class="row">
                       <div class="col-md-6 mt-4">
                           <h5 class="small">
                               <details data-popover="up">
                                   <summary>?</summary>
                                   <div class="popoverBody">
                                       Bitte trage hier deine Gewerbesteuernummer ein.
                                   </div>
                               </details>Gewerbesteuernummer
                           </h5>
                        
                           <input type="text" value="{{ auth()->user() ? auth()->user()->vat : old('meta.vat') }}"
                               class="@error('meta.vat') is-invalid @enderror" placeholder="Gewerbesteuernummer" id="vat"
                               name="meta[vat]" required>
                           @error('meta.vat')
                               <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                               </span>
                           @enderror
                       </div>
                       <div class="col-md-6 mt-4">
                           <h5 class="small">
                               <details data-popover="up">
                                   <summary>?</summary>
                                   <div class="popoverBody">
                                    Änderungen deiner Steuer müssen zu jedem 01.01., spätestens bis 15.01. angezeigt werden.
                                   </div>
                               </details>Steuerkennzeichnung
                           </h5>


                           <select id="meta.is_pay_vat" name="meta[is_pay_vat]">
                               <option value="1" {{ auth()->user()->is_pay_vat == 1 ? 'selected' : '' }}>Regelbesteuert
                               </option>
                               <option value="0" {{ auth()->user()->is_pay_vat == 0 ? 'selected' : '' }}>Kleinunternehmen
                               </option>
                           </select>
                           @error('is_pay_vat')
                               <span class="invalid-feedback" role="alert">
                                   <strong>{{ $message }}</strong>
                               </span>
                           @enderror
                       </div>
                   </div>
               </div>
           @endif


       </div>
       <button class="btn btn-primary mt-4" type="submit">Aktualisieren</button>
   </form>

   @push('scripts')
       <script src="{{ asset('assets/js/summernote-lite.min.js') }}"></script>
       <script>
           $(document).ready(function() {
               $("#beschreibung").summernote();
               $('.dropdown-toggle').dropdown();
           });
       </script>
   @endpush

