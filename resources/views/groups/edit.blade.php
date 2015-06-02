@extends('layouts.main')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2>{{ trans('messages.editcreategroup')}}</h2>
				<p>{{ trans('messages.managemembers') }}<a href="{{ route('groups.members', $group->id) }}">{{ trans('messages.clickhere') }}</a></p>

				<form action="{{ route('groups.edit') }}" method="post">
					{!! csrf_field() !!}
					<input type="hidden" name="_method" value="put">
					@include('groups._form')
				</form>
			</div>
		</div>
	</div>
@endsection
