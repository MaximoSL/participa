@extends('layouts.main')
@section('content')
	<div class="container">
    @include('partials._secondary-nav', [
      'breadcrumbs' => [
        [
          'route' => 'auth.login',
          'label' => trans('messages.login')
        ],
      ]
    ])
    <br><br>
		<div class="row">
			<div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
				<div class="content">
					<h1>{{ trans('messages.login') }}</h1>
					<form action="{{ route('auth.login') }}" method="post">
						{!! csrf_field() !!}
						<!-- Email -->
						<div class="form-group">
							<label for="email">{{ trans('messages.email') }}</label>
							<input class="form-control" id="email" type="text" name="email" value="{{ old('email') }}" placeholder="{{ trans('messages.email') }}">
						</div>
						<!-- Password -->
						<div class="form-group">
							<label for="password">{{ trans('messages.password') }}</label>
							<input class="form-control" id="password" type="password" name="password" placeholder="{{ trans('messages.password') }}">
						</div>
						<!-- Remember checkbox -->
						<div class="checkbox">
							<label>
								<input type="checkbox" name="remember" id="remember" {{ old('remember') ? ' checked' : '' }}> {{ trans('messages.rememberme') }}
							</label>
						</div>
						<!-- Submit -->
						<input class="btn btn-default" type="submit" value="{{ trans('messages.login') }}">
						<br><br>
						<ul class="list-unstyled">
							<li>
								<a class="forgot-password" href="{{ route('password.remind') }}">{{ trans('messages.forgotpassword') }}</a>
							</li>
							<li>
								<a class="forgot-password" href="{{ route('verification.remind') }}">{{ trans('messages.resend') }}</a>
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
			<div class="col-md-10 col-md-offset-1 social-login-wrapper">
			  <div class="row">
			    <div class="col-md-4">
			      <a href="{{ route('auth.connect', 'facebook') }}" class="btn social-login-btn facebook-login-btn">
			        <img src="{{ asset_url('img/icon-facebook.png') }}" alt="facebook icon" />
			        Facebook
			      </a>
			    </div>
			    <div class="col-md-4">
			      <a href="{{ route('auth.connect', 'twitter') }}" class="btn social-login-btn twitter-login-btn">
			        <img src="{{ asset_url('img/icon-twitter.png') }}" alt="twitter icon" />
			        Twitter
			      </a>
			    </div>
			    <div class="col-md-4">
			      <a href="{{ route('auth.connect', 'linkedin') }}" class="btn social-login-btn linkedin-login-btn">
			        <img src="{{ asset_url('img/icon-linkedin.png') }}" alt="linkedin icon" />
			        LinkedIn
			      </a>
			    </div>
			  </div>
			</div>
		</div>
	</div>
@stop
