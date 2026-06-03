@extends('layouts.app',['type'=>'dashboard'])
@section('content')
         <section class="bg-secondary  four-o-four">
             <div class="row">
                 <div class="col-12 col-md-6">
                     <div class="text-container">
                         <h1>400</h1>
                         <p>Your link has expired</p>
                     </div>
                 </div>
                 <div class="col-12 col-md-6">
                     <img src="{{asset('assets/img/icons/404-icon.svg')}}" class="img-fluid" alt="404 Error">
                 </div>
             </div>
         </section>
@endsection