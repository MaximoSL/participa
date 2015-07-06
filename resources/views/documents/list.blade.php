@extends('layouts/main')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ol class="breadcrumb">
					<li><a href="{{ route('home') }}"><i class="icon icon-home"></i> {{ trans('messages.home')}}</a></li>
					<li class="active">{{ trans('messages.document') }}s</li>
				</ol>
			</div>
			<div class="col-md-8 admin-document-list">
				<h2>{{ trans('messages.document') }}s</h2>
        <br>
				<ul>
					@if($doc_count == 0)
						<li>{{ trans('messages.nodocuments') }}</li>
					@else
						{{-- {{ trans('messages.indiedocs') }}: --}}
						@foreach($documents as $doc)
						<li>
							<a href="{{ route('documents.edit', $doc->id) }}">{{ $doc->title }}</a>
						</li>
						@endforeach
					@endif
				</ul>
			</div>
			<div class="col-md-4 admin-add-documents">
				<h3>{{ trans('messages.createdoc') }}</h3>
				<form action="{{ route('documents.create') }}" method="post" id="create-document-form">
					{!! csrf_field() !!}
					<div class="form-group">
						<label for="title">{{ trans('messages.title') }}</label>
						<input class="form-control" id="title" type="text" name="title" value="{{ old('title') }}" placeholder="{{ trans('messages.doctitle') }}">
					</div>
					<input class="btn" type="submit" name="createdoc" value="{{ trans('messages.createdoc') }}">
				</form>
			</div>
		</div>
	</div>
@stop
