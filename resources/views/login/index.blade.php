@extends('layouts.main')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
				<div class="content">
					<h1>{{ trans('messages.login') }}</h1>
					<form action="{{ route('auth.login') }}" method="post">
						{!! csrf_field() !!}
						<!-- Email -->
						<div class="form-group">
							<label for="email">{{ trans('messages.email') }}</label>
							<input class="form-control" id="email" type="text" name="email" value="{{ Input::old('email') }}" placeholder="{{ trans('messages.email') }}">
						</div>
						<!-- Password -->
						<div class="form-group">
							<label for="password">{{ trans('messages.password') }}</label>
							<input class="form-control" id="password" type="password" name="password" placeholder="{{ trans('messages.password') }}">
						</div>
						<!-- Remember checkbox -->
						<div class="checkbox">
							<label for="remember">{{ trans('messages.rememberme') }}</label>
							<input type="checkbox" id="remember" name="remember" value="1" checked="true">
						</div>
						<!-- Submit -->
						<input class="btn btn-default" type="submit" value="{{ trans('messages.login') }}">
						<br><br>
						<ul class="list-unstyled">
							<li>
								<a class="forgot-password" href="{{ route('password/remind') }}">{{ trans('messages.forgotpassword') }}</a>
							</li>
							<li>
								<a class="forgot-password" href="{{ route('verification/remind') }}">{{ trans('messages.resend') }}</a>
							</li>
							<li>
								<a class="forgot-password" href="{{ route('auth.signup') }}" target="_self">{{ trans('messages.signup') }}</a>
							</li>
						</ul>
						<input type="hidden" name="previous_page" value="{{ $previous_page }}">
					</form>
				</div>
			</div>
		</div>
		<div class="row">
			<div social-login message="{{ trans('messages.sociallogin') }}"></div>
		</div>
	</div>
@endsection
