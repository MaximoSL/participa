@extends('layouts.main')

@section('content')
	<div class="container">
		<div class="row">
			<ol class="breadcrumb">
				<li><a href="{{ route('dashboard') }}">{{ trans('messages.dashboard') }}</a></li>
				<li class="active">{{ trans('messages.document') }}s</li>
			</ol>
		</div>
		<div class="row content" ng-controller="DashboardDocumentsController">
			<div class="col-md-8 admin-document-list">
				<div class="row">
					<div class="col-md-12">
						<h2>{{ trans('messages.document') }}s</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<input type="text" ng-model="docSearch" class="form-control" placeholder="Filter document titles">
					</div>
					<div class="col-md-4">
						<select ui-select2="select2Config" ng-model="select2">
							<option value=""></option>
							<optgroup label="Category">
								<option value="category_@{{ category.id }}" ng-repeat="category in categories">@{{ category.name }}</option>
							</optgroup>
							<optgroup label="Sponsor">
								<option value="sponsor_@{{ sponsor.id }}" ng-repeat="sponsor in sponsors">@{{ sponsor.fname }} @{{ sponsor.lname }}</option>
							</optgroup>
							<optgroup label="Status">
								<option value="status_@{{ status.id}}" ng-repeat="status in statuses">@{{ status.label}}</option>
							</optgroup>
						</select>
					</div>
					<div class="col-md-2">
						<select ui-select2="dateSortConfig" id="dateSortSelect" ng-model="dateSort">
							<option value=""></option>
							<option value="created_at">{{ trans('messages.posted') }}</option>
							<option value="updated_at">{{ trans('messages.updated') }}</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<ul>
							<li ng-repeat="doc in docs | toArray | filter:docSearch | orderBy:dateSort:reverse" ng-show="docFilter(doc)">
								<a href="/dashboard/docs/@{{ doc.id }}">@{{ doc.title }}</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-4 admin-add-documents">
				<div class="row">
					<h2>{{ trans('messages.createdoc')}}</h2>
					<form class="" action="{{ route('dashboard.docs') }}" method="post">
						{!! csrf_field() !!}
						<div class="form-group">
							<label for="title">Title</label>
							<input class="form-control" type="text" name="title" value="{{ old('title') }}" placeholder="Document Title">
						</div>
						<input class="btn" type="submit" value="Create Document">
					</form>
				</div>
			</div>
		</div>
	</div>
@stop
