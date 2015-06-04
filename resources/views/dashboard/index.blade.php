@extends('layouts/main')
@section('content')
	<div class="container">
		<div class="row">
			<ol class="breadcrumb">
				<li class="active">{{ trans('messages.dashboard') }}</li>
			</ol>
			<div class="col-md-12">
				<div class="content">
					<ul class="list-unstyled">
						<li><a href="{{ route('dashboard.settings') }}">{{ trans('messages.settings') }}</li>
						<li><a href="{{ route('dashboard.docs') }}">{{ trans('messages.createeditdocs') }}</li>
						<li><a href="{{ route('dashboard.notifications') }}">{{ trans('messages.notifications') }}</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
@stop
