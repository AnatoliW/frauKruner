 <form method="post" action="{{ route('address.update') }}">
     @csrf
     <div class="row mt-4">
         <h5 class="small">
             <details data-popover="up">
                 <summary>?</summary>
                 <div class="popoverBody">
                     Diese Infos sind ausschließlich privat. Sie dienen der Rechnungserstellung und sind nur für dich
                     sichtbar. Gebe hier deine realen Daten an.
                 </div>
             </details>Adressdaten
         </h5>

         <div class="col-12 col-md-6 mt-4 d-none">

             <input type="text" id="vorname" name="first_name"
                 value="{{ $data->first_name ? $data->first_name : Auth()->user()->name }}" placeholder="Vorname">
             @error('first_name')
                 <span class="text-danger">
                     {{ $message }}
                 </span>
             @enderror
         </div>

         <div class="col-12 col-md-6 mt-4 d-none">
             <input type="text" id="nachname" name="last_name"
                 value="{{ $data->last_name ? $data->last_name : Auth()->user()->last_name }}" placeholder="Nachname">
             @error('last_name')
                 <span class="text-danger">
                     {{ $message }}
                 </span>
             @enderror
         </div>

         <div class="col-12 mt-4 d-flex text-muted">
             <p class="me-1">{{ $data->first_name ? $data->first_name : Auth()->user()->name }}</p>
             <p>{{ $data->last_name ? $data->last_name : Auth()->user()->last_name }}</p>
         </div>

         <div class="col-12">
             <input type="text" id="zusatz" name="additional" value="{{ $data->additional ?? old('additional') }}"
                 placeholder="Zusatz">
             @error('additional')
                 <span class="text-danger">
                     {{ $message }}
                 </span>
             @enderror
         </div>
         @if (Auth()->user()->role_id == 3)
             <div class="col-12 mt-4">
                 <label for="paypal_email">E-Mail</label>
                 <input type="email" id="email" name="email" value="{{ Auth()->user()->email }}"
                     placeholder="E-Mail">
                 @error('paypal_email')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif

         @if (Auth()->user()->role_id == 3)
             <div class="col-12 mt-4">
                 <label for="iban">IBAN</label>
                 <input type="text" id="iban" name="iban" value="{{ Auth()->user()->method->iban ?? null }}"
                     placeholder="IBAN">
                 @error('iban')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
             <div class="col-12 mt-4">
                 <label for="bic">BIC</label>
                 <input type="text" id="bic" name="bic" value="{{ Auth()->user()->method->bic ?? null }}"
                     placeholder="BIC">
                 @error('bic')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif
         @if (Auth()->user()->role_id == 3)
             <div class="col-8 mt-4">
                 <input type="text" id="straße" name="street"
                     value="{{ old('street') ?? (Auth()->user()->address?->street ?? Auth()->user()->verification?->street) }}"
                     placeholder="Straße">

                 @error('street')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @else
             <div class="col-8 mt-4">
                 <input type="text" id="straße" name="street" placeholder="Straße"
                     value="{{ $data->street ?? old('street') }}">
                 @error('street')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif
         @if (Auth()->user()->role_id == 3)
             <div class="col-4 mt-4">
                 <input type="text" id="anschriftnummer" name="house_no"
                     value="{{ old('house_no') ?? (Auth()->user()->address?->house_no ?? Auth()->user()->verification?->house_no) }}"
                     placeholder="Nr">

                 @error('house_no')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @else
             <div class="col-4 mt-4">
                 <input type="text" id="anschriftnummer" name="house_no"
                     value="{{ $data->house_no ?? old('house_no') }}" placeholder="Nr">
                 @error('house_no')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif

         @if (Auth()->user()->role_id == 3)
             <div class="col-4 mt-4">
                 <input type="text" id="plz" name="zip"
                     value="{{ old('zip') ?? (Auth()->user()->address?->zip ?? Auth()->user()->verification?->zip) }}"
                     placeholder="PLZ">

                 @error('zip')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @else
             <div class="col-4 mt-4">
                 <input type="number" id="plz" value="{{ $data->zip ?? old('zip') }}" name="zip"
                     placeholder="PLZ">
                 @error('zip')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif


         <div class="col-8 mt-4">
             <input type="text" id="bundesland" name="federal_state"
                 value="{{ $data->federal_state ?? old('federal_state') }}" placeholder="Ort">
             @error('federal_state')
                 <span class="text-danger">
                     {{ $message }}
                 </span>
             @enderror
         </div>
         {{--
         <div class="col-6 mt-4">
             <input type="text" id="postfach" name="po_box" placeholder="Postfach"
                 value="{{ $data->po_box ?? old('po_box') }}">
             @error('po_box')
                 <span class="text-danger">
                     {{ $message }}
                 </span>
             @enderror
         </div>
         --}}
         @if (Auth()->user()->role_id == 3)
             <div class="col-6 mt-4">
                 <input type="text" id="ustident" name="vat_id" placeholder="USt.-Ident.-Nr."
                     value="{{ $data->vat_id ?? old('vat_id') }}">
                 @error('vat_id')
                     <span class="text-danger">
                         {{ $message }}
                     </span>
                 @enderror
             </div>
         @endif


     </div>
     <button class="btn btn-primary mt-4" type="submit">Aktualisieren</button>
 </form>
