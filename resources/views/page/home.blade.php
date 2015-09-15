@include('layouts.comscore')
<div class="main-banner">
  <div class="container">
    <h1 class="hidden-sm hidden-xs"><strong>gob<span class="red">.</span>mx/{{ config('app.base_name') }}</strong></h1>
    <h1 class="text-small hidden-md hidden-lg"><strong>gob<span class="red">.</span>mx/{{ config('app.base_name') }}</strong></h1>
    <p class="text-sub">Una plataforma de participación ciudadana, que te permite a través de foros, encuestas y ejercicios de co-edición crear mejores propuestas de política pública en México.</p>
    <p  class="text-sub-2"><strong>gob.mx/{{ config('app.base_name') }}</strong> cuenta con tres herramientas <br>de participación: <strong>Encuesta</strong> + <strong>Foro</strong> + <strong>Co-Edición</strong></p>
  </div>
</div>

<div class="home-docs container">
  @include('partials._secondary-nav')

	<div class="row">
		<div class="col-lg-12 col-md-12">
			<h4>Temas y/o encuestas de participación</h4>
			<hr class="red">
			<div ng-controller="HomePageController">
				<div class="home-docs-filters row">
          <div class="col-sm-6">
						<form ng-submit="search()">
							<input id="doc-text-filter" type="text" ng-model="docSearch" class="form-control" placeholder="{{ trans('messages.filter') }}">
						</form>
					</div>
					<div class="col-sm-3 home-select2-container">
						<select class="form-control" id="home-select2-filter" ng-model="docFilter" ng-change="search()">
              <option value=""></option>
							<optgroup label="{{ trans('messages.category') }}">
                @foreach($categories as $category)
								  <option value="docCategories:{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
							</optgroup>
              <optgroup label="{{ trans('messages.institution') }}">
                @foreach($institutions as $institution)
                  <option value="docInstitutions:{{ $institution->id }}">{{ $institution->name }}</option>
                @endforeach
              </optgroup>
							<optgroup label="{{ trans('messages.status') }}">
                @foreach($statuses as $status)
								<option value="statuses:{{ $status->id }}">{{ $status->label }}</option>
                @endforeach
							</optgroup>
						</select>
					</div>
					<div class="col-sm-3 home-select2-container">
						<select class="form-control" id="home-select2-order" ng-model="docOrder" ng-change="search()">
							<option value=""></option>
							<option value="created_at">{{ trans('messages.posted') }}</option>
							<option value="updated_at">{{ trans('messages.updated') }}</option>
						</select>
					</div>
				</div>
        <div class="docs-list list-unstyled">
					<p ng-show="updating">Cargando...</p>
					<div ng-repeat="doc in docs | toArray" ng-show="!updating">
						<div doc-list-item></div>
					</div>
					<div class="docs-pagination">
						<pagination class="pagination-sm" max-size="10" boundary-links="true" rotate="false" total-items="totalDocs" items-per-page="perPage" previous-text="&lsaquo;" next-text="&rsaquo;" first-text="&laquo;" last-text="&raquo;" ng-model="page" ng-change="paginate()"></pagination>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
