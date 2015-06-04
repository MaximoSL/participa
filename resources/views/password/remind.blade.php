@extends('layouts/main')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
			<div class="content">
				<h1>{{ trans('messages.forgotpassword') }}</h1>
				<form class="reset-form" action="{{ action('RemindersController@postRemind') }}" method="POST">
					{!! csrf_field() !!}
					<p>{{ trans('messages.premindpass') }}</p>
					<div class="form-group">
				    	<label for="email">{{ trans('messages.emailaddress') }}</label>
				    	<input id="email" type="email" name="email" placeholder="email@example.com" class="form-control" />
				    </div>
				    <div class="form-group">
				    	<input class="btn btn-default" type="submit" value="{{ trans('messages.sendpassreset') }}">
				    </div>
				</form>
			</div>
		</div>
	</div>
</div>
@stop
