<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'CRM') }}</title>

	<!-- Fonts -->
	<link rel="dns-prefetch" href="//fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/style.css') }}" rel="stylesheet">

	<script src="https://unpkg.com/phosphor-icons"></script>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<!-- VUEJS -->
	<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

</head>
<body>
	<div id="app">
		<nav class="navbar fixed-top navbar-expand-md navbar-light bg-dark shadow-sm">
			<div class="container-fluid">
				<a class="navbar-brand text-info" href="{{ url('/') }}">
					{{ config('app.name', 'Laravel') }}
				</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<!-- Right Side Of Navbar -->
					<ul class="navbar-nav ms-auto">
						<!-- Authentication Links -->
						@guest
							@if (Route::has('login'))
								<li class="nav-item">
									<a class="nav-link text-light" href="{{ route('login') }}">{{ __('Login') }}</a>
								</li>
							@endif

							@if (Route::has('register'))
								<li class="nav-item">
									<a class="nav-link text-light" href="{{ route('register') }}">{{ __('Register') }}</a>
								</li>
							@endif
						@else
							<li class="nav-item dropdown">
								<a id="navbarDropdown" class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
									{{ Auth::user()->name }}
								</a>

								<div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
									<a class="dropdown-item" href="{{ route('creator') }}" >Creator</a>
									<a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
									<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
										@csrf
									</form>
								</div>
							</li>
						@endguest
					</ul>
				</div>
			</div>
		</nav>
		<main>
			<div class="container-fluid">
				<div class="row">
					<left-nav-component></left-nav-component>
					<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4 pb-2">
						@yield('content')
					</div>
				</div>
			</div>
		</main>
	</div>
	<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="errorModalLabel">Error!</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="errorModalBodyContents"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script>
	// Assign the session variable to a JavaScript variable
	let siteUserObject = null;
	<?php if(\Illuminate\Support\Facades\Auth::check() ) :?>
	siteUserObject = {!! \Illuminate\Support\Facades\Auth::user() !!};
	<?php endif;?>
	
	let errors = {};
	@if(session('errors'))
		errors = {!! json_encode(session('errors')->all()) !!};
	@else
		@isset($errorMessage)
			errors = {!! $errorMessage !!};
		@endisset
	@endif

	const csrfToken = "{{ @csrf_token() }}";
	</script>
	<!-- Scripts -->
	<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
