@extends('layouts/main')

@section('content')
<div class="main-banner">
	<div class="container">
		<h1><strong>gob<span class="red">.</span>mx/participa</strong></h1>
		<p class="text-sub">Una plataforma de participación ciudadana, que te permite a través de foros, encuestas y ejercicios de co-edición crear mejores propuestas de política pública en México.</p>
		<p  class="text-sub-2"><strong>gob.mx/participa</strong> cuenta con tres herramientas <br>de participación: <strong>Encuesta</strong> + <strong>Foro</strong> + <strong>Co-Edición</strong></p>
	</div>
</div>

<div class="home-docs container">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<h4>Temas y/o encuestas de participación</h4>
			<hr class="red">
			<div>
				<div class="home-docs-filters row">
					<div class="col-sm-6">
						<input tourtip="@{{ step_messages.step_1 }}" tourtip-step="1" tourtip-next-label="Siguiente" id="doc-text-filter" type="text" ng-model="docSearch" class="form-control" placeholder="{{ trans('messages.filter') }}">
					</div>
					<div class="col-sm-4 home-select2-container">
						<select id="doc-category-filter" ui-select2="select2Config" ng-model="select2">
							<option value=""></option>
							<optgroup label="{{ trans('messages.category') }}">
								<option value="category_@{{ category.id }}" ng-repeat="category in categories">@{{ category.name }}</option>
							</optgroup>
							<optgroup label="{{ trans('messages.sponsor') }}">
								<option value="sponsor_@{{ sponsor.id }}" ng-repeat="sponsor in sponsors">@{{ sponsor.fname }} @{{ sponsor.lname }}</option>
							</optgroup>
							<optgroup label="{{ trans('messages.status') }}">
								<option value="status_@{{ status.id}}" ng-repeat="status in statuses">@{{ status.label}}</option>
							</optgroup>
						</select>
					</div>
					<div class="col-sm-2 home-select2-container">
						<select id="doc-date-filter" ui-select2="dateSortConfig" id="dateSortSelect" ng-model="dateSort">
							<option value=""></option>
							<option value="created_at">{{ trans('messages.posted') }}</option>
							<option value="updated_at">{{ trans('messages.updated') }}</option>
						</select>
					</div>
				</div>
				<div class="docs-list list-unstyled">
					@foreach($docs as $doc)
						@include('doc._doc-item')
					@endforeach
					{!! $docs->render() !!}
				</div>
			</div>
		</div>
	</div>
</div>
@stop
