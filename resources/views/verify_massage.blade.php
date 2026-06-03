@extends('layouts.app')
@section('title','Vielen Dank für deine Registrierung! ')
@section('content')

<div class="container my-5">
   <div class="row">
       <div class="col-md-12 text-center">
			<div class="thanks">
		         <img src="{{ asset('images/check.png') }}" alt="" height="150px">
				<h2 class="mt-3">Vielen Dank für deine Registrierung! <br> Bitte bestätige deine E-Mail!</h2>
			</div>
	   </div>
   </div>
</div>


@endsection
@section('javascript')
<script type="text/javascript">
$(document).ready(function() {

});
</script>

@endsection
