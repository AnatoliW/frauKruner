@extends('layouts.app')
@section('title','Dashboard')
@section('css')
  <link rel="stylesheet" href="{{asset('css/custom/account.css')}}">
@stop
@section('content')
<section class="account-part">
            <div class="container">
			<div class="row">
                    <div class="col-lg-12">
                        <div class="account-menu">
                            <ul class="nav nav-tabs">
                                <li><a href="#dash" class="nav-link active" data-toggle="tab">Dashboard</a></li>
                                <li><a href="#order" class="nav-link" data-toggle="tab">Orders</a></li>
                                <li><a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a></li>
								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
									@csrf
								</form>								
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- dashboard content -->
                <div class="tab-pane active" id="dash">
					<div class="row justify-content-center">
						<div class="col-md-10">
							<div class="card">
								<div class="card-header">Dashboard</div>
								<div class="card-body">
									<form method="POST" action="{{ route('user.update') }}">
									@csrf
										<div class="row"> 
											<div class="col-md-6"> 
											   <div class="form-group">
												<label for="first_name">Vorname</label>
												<input value="{{Auth::user()->first_name}}" type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" placeholder="Vorname" name="first_name" >
												@error('first_name')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>
											<div class="col-md-6"> 
											   <div class="form-group">
												<label for="last_name">Nachname</label>
												<input value="{{Auth::user()->last_name}}" type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" placeholder="Nachname" name="last_name"  >
												@error('last_name')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							  
											  </div>					
											</div>
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="email">E-Mail</label>
												<input value="{{Auth::user()->email}}"  type="text" class="form-control" id="email" placeholder="E-Mail" readonly>
											  </div>					
											</div>
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="address">Adresse</label>
												<input value="{{Auth::user()->address}}" type="text" class="form-control @error('address') is-invalid @enderror" id="address" placeholder="Adresse" name="address" >
												@error('address')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							  
											  </div>					
											</div>
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="state">Ort</label>
												<input value="{{Auth::user()->state}}" type="text" class="form-control @error('state') is-invalid @enderror" id="state" placeholder="Ort" name="state" >
												@error('state')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							  
											  </div>					
											</div>							
											<div class="col-md-6"> 
											   <div class="form-group">
												<label for="city">Ort</label>
												<input value="{{Auth::user()->city}}" type="text" class="form-control @error('city') is-invalid @enderror" id="city" placeholder="Ort" name="city" >
												@error('city')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>
											<div class="col-md-6"> 
											   <div class="form-group">
												<label for="post_code">PLZ</label>
												<input value="{{Auth::user()->post}}" type="number" step="1" class="form-control @error('post_code') is-invalid @enderror" id="post_code" placeholder="PLZ" name="post_code" >
												@error('post_code')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>
											<div class="col-md-6"> 
											   <div class="form-group">
												<button class="btn btn-inline" type="submit"> Senden</button>
											  </div>					
											</div>
										</div>
									</form>
									<h3 class="mt-3 mb-3">Passwort ändern</h3>
									<form method="POST" action="{{ route('change.password') }}">
										@csrf
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="current_password">Jetziges Passwort</label>
												<input  type="password"  class="form-control @error('current_password') is-invalid @enderror" id="current_password" placeholder="Jetziges Passwort" name="current_password">
												@error('current_password')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>	
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="new_password">Neues Passwort</label>
												<input  type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" placeholder="Neues Passwort" name="new_password">
												@error('new_password')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>		
											<div class="col-md-12"> 
											   <div class="form-group">
												<label for="new_confirm_password">Passwort bestätigen</label>
												<input  type="password" class="form-control @error('new_confirm_password') is-invalid @enderror" id="new_confirm_password" placeholder="Passwort bestätigen" name="new_confirm_password">
												@error('new_confirm_password')
													<span class="invalid-feedback" role="alert">
														<strong>{{ $message }}</strong>
													</span>
												@enderror							 
											 </div>					
											</div>		
											<div class="col-md-12"> 
											   <div class="form-group">
													<button class="btn btn-inline" type="submit">Senden</button>						 
											   </div>					
											</div>						
									 </form>						
								</div>
							</div>
						</div>
					</div>
                </div>

                <!-- order content -->
                <div class="tab-pane" id="order">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-heading">
                                <h2>Bestellhistorie</h2>
                            </div>
                        </div>
                        <div class="col-lg-12">
						@if($orders->count() >0)
                            <div class="order-content">
                                <table class="table-list">
                                    <thead>
                                        <tr>
                                            <th scope="col">Bestellung</th>
                                            <th scope="col">Datum</th>
                                            <th scope="col">Gesamt</th>
                                            <th scope="col">Aktion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									@foreach($orders as $order)
                                        <tr>
                                            <td class="table-order"><p>{{$order->id}}</p></td>
                                            <td class="table-date"><p>{{$order->created_at->format('d M Y')}}</p></td>
                                            <td class="table-total"><p>{{Shop::price($order->total)}}</p></td>
                                            <td class="table-action">
                                                <a target="_blank" href="{{route('invoice', ['order'=>$order->id])}}"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
									@endforeach
                                    </tbody>
                                </table>
                            </div>
						@else
						<h3 class="text-center"> You did not placed any order </h3>
						@endif								
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection
