@extends('layouts.main')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2>{{ trans('messages.editcreategroup')}}</h2>
				<form action="{{ route('groups.edit') }}" method="post">
					{!! csrf_field() !!}
					@include('groups._form')
				</form>
			</div>
		</div>
	</div>
@endsection
