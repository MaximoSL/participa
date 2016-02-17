@extends('layouts.main')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2>{{ trans('messages.editprofile') }}</h2>
				<form class="" action="{{ route('user.account') }}" method="post">
					{!! csrf_field() !!}
					<input type="hidden" name="_method" value="patch">
					<div class="form-group">
						<!-- Change avatar at gravatar.com -->
						<img src="{{ $user->gravatar() }}" alt="{{ $user->name }}" />
						<a href="https://gravatar.com" target="_blank">{{ trans('messages.chggravatar') }} Gravatar.com</a>
					</div>
					<!-- First Name -->
					<div class="form-group">
						<label for="fname">{{ trans('messages.fname') }}:</label>
						<input type="text" class="form-control" name="fname" id="fname" placeholder="{{ trans('messages.enterfname') }}" value="{{ $user->fname }}"/>
					</div>
					<!-- Last Name -->
					<div class="form-group">
						<label for="fname">{{ trans('messages.lname') }}:</label>
						<input type="text" class="form-control" name="lname" id="lname" placeholder="{{ trans('messages.enterlname') }}" value="{{ $user->lname }}"/>
					</div>
					<!-- Email -->
					<div class="form-group">
						<label for="email">{{ trans('messages.emailaddress') }}:</label>
						<input type="email" class="form-control" name="email" id="email" placeholder="{{ trans('messages.enteremail') }}" value="{{ $user->email}}"/>
					</div>
					<!-- URL -->
					<div class="hidden">
						<label for="url">URL:</label>
						<input type="url" class="form-control" name="url" id="url" placeholder="{{ trans('messages.enterurl') }}" value="{{ $user->url }}"/>
					</div>
					<!-- Phone -->
					<div class="hidden">
						<label for="phone">{{ trans('messages.phone') }}:</label>
						<input type="tel" class="form-control" name="phone" id="phone" placeholder="{{ trans('messages.enterphone') }}" value="{{ $user->phone }}"/>
					</div>
					<!-- TODO: Organization -->
					<!-- Location -->
					<!-- TODO: autofill / check location exists -->
					<!-- Password -->
					<div class="form-group">
						<label for="password_1">{{ trans('messages.chgpass') }}:</label>
						<input type="password" class="form-control" name="password_1" id="password_1" placeholder="{{ trans('messages.newpass') }}" value=""/>
					</div>
					<div class="form-group">
						<label for="password_2">{{ trans('messages.cnfpass') }}:</label>
						<input type="password" class="form-control" name="password_2" id="password_2" placeholder="{{ trans('messages.repeatpass') }}" value=""/>
					</div>
					<div class="checkbox">
						@if($user->verified())
							<label>
								<input name="verify" id="verify" type="hidden" checked disabled> <!-- {{ trans('messages.reqveraccount') }} is '{{ $user->verified() }}' -->
							</label>
						@else
							<label>
								<input name="verify" id="verify" type="hidden"> <!-- {{ trans('messages.reqveraccount') }}' -->
							</label>
						@endif
					</div>
					<button type="submit" class="btn btn-primary" id="submit">{{ trans('messages.submit') }}</button>
				</form>
			</div>
		</div>
	</div>
@stop
