<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<form action="{{ route('api.auth.login') }}" method="post" id="login">
			{!! csrf_field() !!}
			<div class="errors"></div>
			<!-- Email -->
			<div class="form-group">
				<label for="email">{{ trans('messages.email') }}</label>
				<input class="form-control" type="text" name="email" value="{{ old('email') }}" placeholder="{{ trans('messages.email') }}">
			</div>
			<!-- Password -->
			<div class="form-group">
				<label for="email">{{ trans('messages.password') }}</label>
				<input class="form-control" type="password" name="password" value="" placeholder="{{ trans('messages.password') }}">
			</div>
			<!-- Submit -->
			<input class="btn btn-default" type="submit" value="{{ trans('messages.login') }}">
			<br>
			<a class="forgot-password" href="{{ route('password.remind') }}">{{ trans('messages.forgotpassword') }}</a>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-md-12 social-login-wrapper">
	  <div class="row">
	    <div class="col-md-12">
	      <a href="{{ route('auth.connect', 'facebook') }}" class="btn social-login-btn facebook-login-btn">
	        <img src="{{ url('img/icon-facebook.png') }}" alt="facebook icon" />
	        {{ trans('messages.loginwith') }} Facebook
	      </a>
	    </div>
	    <div class="col-md-12">
	      <a href="{{ route('auth.connect', 'twitter') }}" class="btn social-login-btn twitter-login-btn">
	        <img src="{{ url('img/icon-twitter.png') }}" alt="twitter icon" />
	        {{ trans('messages.loginwith') }} Twitter
	      </a>
	    </div>
	    <div class="col-md-12">
	      <a href="{{ route('auth.connect', 'linkedin') }}" class="btn social-login-btn linkedin-login-btn">
	        <img src="{{ url('img/icon-linkedin.png') }}" alt="linkedin icon" />
	        {{ trans('messages.loginwith') }} LinkedIn
	      </a>
	    </div>
	  </div>
	</div>
</div>
