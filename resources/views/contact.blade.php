@extends('layouts.app')
@section('title','Kontakt')
@section('css')
   <link rel="stylesheet" href="{{asset('css/custom/contact.css')}}">
@endsection
@section('content')
<section class="single-banner">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="single-content">
					<h2>Contact us</h2>
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
						<li class="breadcrumb-item active" aria-current="page">Contact</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="contact-part">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="contact-heading">
                            <h1 class="title">Get in Touch</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="contact-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <h2 class="title">Find us</h2>
                            <p>{!!setting('site.address')!!}</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="contact-info">
                            <i class="fas fa-phone-alt"></i>
                            <h2 class="title">Make a Call</h2>
                            <p>{!!setting('site.phone')!!}</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="contact-info">
                            <i class="fas fa-envelope"></i>
                            <h2 class="title">Send Mail</h2>
                            <p>{!!setting('site.email')!!}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <form class="contact-form" action="{{route('contact.store')}}" method="post">
						@csrf
                            <label class="contact-label">
                                <input type="text" placeholder="Your Name" name="name">
                                <i class="fas fa-user"></i>
                            </label>
                            <label class="contact-label">
                                <input type="text" placeholder="Your Email" name="email">
                                <i class="fas fa-envelope"></i>
                            </label>
                            <label class="contact-label">
                                <input type="text" placeholder="Your Subject" name="subject">
                                <i class="fas fa-bookmark"></i>
                            </label>
                            <label class="contact-label">
                                <textarea name="message" placeholder="Your Message"></textarea>
                                <i class="fas fa-pen-nib"></i>
                            </label>
                            <button class="btn btn-inline">
                                <i class="fas fa-paper-plane"></i>
                                <span>send message</span>
                            </button>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <div class="contact-map">
						{!! setting('site.map') !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
@endsection
