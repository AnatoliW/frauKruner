<?php $i=0; ?>
@if(!empty($errors) && count($errors) > 0)
@foreach($errors->all() as $error)
	<div class="toast bg-danger mt-2 mr-2" style="position: absolute; top: <?php echo $i ; ?>px	; right: 0;z-index:11" data-delay="6000">
		<div class="toast-header bg-danger">
			<strong class="mr-auto text-white">Fehler</strong>
			<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
			   <span aria-hidden="true">&times;</span>
			</button>
		</div> 
		<div class="toast-body text-white">
		  {{ $error }}
		</div>
	 </div>  
<?php $i=$i+80; ?>	 
@endforeach
@endif
@if(session()->has('success_msg'))
	<div class="toast bg-info mt-2 mr-2" style="position: absolute; top: 0; right: 0;z-index:11" data-delay="6000">
		<div class="toast-header bg-info">
			<strong class="mr-auto text-white">Erfolgreich</strong>
			<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
			   <span aria-hidden="true">&times;</span>
			</button>
		</div> 
		<div class="toast-body text-white">
		  {{ session()->get('success_msg') }}
		</div>
	 </div>  
@endif

