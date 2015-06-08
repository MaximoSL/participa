<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<form class="" action="{{ route('api.auth.signup') }}" method="post">
			{!! csrf_field() !!}
			<!-- First Name -->
			<div class="form-group">
				<label for="fname">{{ trans('messages.fname') }}</label>
				<input class="form-control" id="fname" type="text" name="fname" value="{{ old('fname') }}" placeholder="{{ trans('messages.fname') }}">
			</div>
			<!-- Last Name -->
			<div class="form-group">
				<label for="lname">{{ trans('messages.lname') }}</label>
				<input class="form-control" id="lname" type="text" name="lname" value="{{ old('lname') }}" placeholder="{{ trans('messages.lname') }}">
			</div>
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
			<!-- Submit -->
			<input class="btn btn-default" type="submit" value="{{ trans('messages.signup') }}">
		</form>
	</div>
</div>
<div class="row">
	<div class="col-md-12 social-login-wrapper">
	  <div class="row">
	    <div class="col-md-12">
	      <a href="/participa/user/facebook-login" class="btn social-login-btn facebook-login-btn">
	        <img src="/participa-assets/img/icon-facebook.png" alt="facebook icon" />
	        {{ trans('messages.signupwith') }} Facebook
	      </a>
	    </div>
	    <div class="col-md-12">
	      <a href="/participa/user/twitter-login" class="btn social-login-btn twitter-login-btn">
	        <img src="/participa-assets/img/icon-twitter.png" alt="twitter icon" />
	        {{ trans('messages.signupwith') }} Twitter
	      </a>
	    </div>
	    <div class="col-md-12">
	      <a href="/participa/user/linkedin-login" class="btn social-login-btn linkedin-login-btn">
	        <img src="/participa-assets/img/icon-linkedin.png" alt="linkedin icon" />
	        {{ trans('messages.signupwith') }} LinkedIn
	      </a>
	    </div>
	  </div>
	</div>
</div>
