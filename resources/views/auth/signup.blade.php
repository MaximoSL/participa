@extends('layouts/main')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
				<div class="content">
					<h1>{{ trans('messages.signup') }}</h1>
					<form class="" action="{{ route('user.signup') }}" method="post">
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
		</div>
		<div class="row">
			<div social-login message="{{ trans('messages.signupwith') }}"></div>
		</div>
	</div>
@endsection
