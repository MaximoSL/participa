@extends('layouts/main')
@section('content')
<div class="container">
	<div class="row">
		<ol class="breadcrumb">
			<li><a href="{{ route('dashboard') }}">{{ trans('messages.dashboard') }}</a></li>
			<li class="active">{{ trans('messages.notifications') }}</li>
		</ol>
	</div>
	<div class="row content">
		<h1>{{ trans('messages.notifications') }}</h1>
		<p>{{ trans('messages.selectnotif') }}</p>
		<form action="{{ route('dashboard.notifications') }}" method="post">
			{!! csrf_field() !!}
			@foreach($validNotifications as $key => $value)
			<div class="form-group">
				<label for="{{ $key }}">{{ $value }}</label>
				<input type="checkbox" name="{{ $key }}" value="{{ $value }}" @if(in_array($key, $selectedNotifications)) checked @endif>
			</div>
			@endforeach
			<input type="submit" class="btn btn-default" value="{{ trans('message.savesettings') }}">
		</form>
	</div>
</div>
@stop
