@extends('layouts.main')

@section('content')
<div class="modal fade" id="annotationThanks" tabindex="-1" role="dialog" aria-labelledby="annotationThanks" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    </div>
  </div>
</div>
<div class="document-wrapper" ng-controller="DocumentPageController">
  <div class="container">

    <div class="row" ng-controller="ReaderController" ng-init="init({{ $doc->id }})">
      <div class="col-md-8">
        <div class="doc-head">
          <h1>{{ trans('messages.cofemer-layout-header') }}</h1>
          <ul class="list-unstyled">
            <li>
              <small>{{ trans('messages.dependency') }}: {{ $doc->group_name }}</small>
            </li>
            <li>
              <small>@{{ 'POSTED' | translate }}: @{{ doc.created_at | date: 'longDate' }}, @{{ doc.created_at | date: 'HH:mm:ss' }}</small>
            </li>
            <li>
              <small>@{{ 'UPDATED' | translate }}: @{{ doc.updated_at | date: 'longDate' }}, @{{ doc.updated_at | date: 'HH:mm:ss' }}</small>
            </li>
            <li>
              <small>Fecha de cierre: 8 de junio de 2015, 18:00:00</small>
            </li>

          </ul>
          <div class="doc-extract" ng-if="introtext">
            <div class="markdown" data-ng-bind-html="introtext"></div>
          </div>

          <div><strong>{{ trans('messages.cofemer-ask-more-info') }}</strong></div>
          <div>{{ trans('messages.cofemer-contact-info') }}</div>

          <br />
          <div><strong>{{ trans('messages.cofemer-note-to-users') }}</strong></div>

          <div class="doc-actions">
            <br>
            <p>{{ trans('messages.supportdoctext') }}</p>
            <a id="doc-support" href="#" class="btn btn-primary" ng-click="support(true, $event)" ng-class="{'btn-success': supported}">
              <span class="glyphicon glyphicon-ok"></span>
              {{ trans('messages.supportdoc') }}
            </a>
            <a id="doc-oppose" href="#" class="btn btn-default" ng-click="support(false, $event)" ng-class="{'btn-danger': opposed}">
              <span class="glyphicon glyphicon-remove"></span>
              {{ trans('messages.opposedoc') }}
            </a>
          </div>

        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <ul class="nav nav-tabs" role="tablist" tourtip="@{{ step_messages.step_3 }}" tourtip-step="3" tourtip-next-label="Siguiente">
          <li ng-class="{'active':secondtab == false}"><a href="#tab-discussion" target="_self" role="tab" data-toggle="tab">Comentarios a la Propuesta</a></li>
          <a href="{{ route('docs.feed', $doc->slug) }}" class="rss-link" target="_self"><img src="{{ url('img/rss-fade.png') }}" class="rss-icon" alt="RSS Icon"></a>
        </ul>

        <div class="tab-content">
          <div id="tab-discussion" ng-class="{'active': secondtab == false}" class="tab-pane">
            <div class="doc-forum" ng-controller="CommentController" ng-init="init({{ $doc->id }}, false, false)">
              @include('doc.reader.cofemer.comments')
            </div>
          </div>
        </div>

      </div>
      <div class="col-md-4">
        <div class="doc-content-sidebar hide">
          <div class="sidebar-unit">
            <h4>{{ trans('messages.howtoparticipate') }}</h4>
            <hr class="red">
            <ol>
              <li>{{ trans('messages.readpolicy') }}</li>
              <li>{{ trans('messages.signupnaddvoice') }}</li>
              <li>{{ trans('messages.anncommsuppopp') }}</li>
            </ol>
            <img src="{{ url('img/como-comentar.gif') }}" class="how-to-annotate-img img-responsive" />
          </div>

          <div class="sidebar-unit" ng-controller="DocumentTocController" ng-show="headings.length > 0">
            <h4>{{ trans('messages.tableofcontents') }}</h4>
            <hr class="red">
            <div id="toc-container">
              <ul class="list-unstyled doc-headings-list">
                <li ng-repeat="heading in headings">
                  <a class="toc-heading toc-@{{ heading.tag | lowercase }}" href="#@{{ heading.link }}">@{{ heading.title }}</a>
                </li>
              </ul>
            </div>
          </div>

          <div class="sidebar-unit">
            <h4>{{ trans('messages.annotations') }}</h4>
            <hr class="red">
            <div ng-controller="AnnotationController" ng-init="init({{ $doc->id }})" class="rightbar participate">
              @include('doc.reader.annotations')
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
  <script>
    var user = {
      id: '{{ $loggedUser ? $loggedUser->id : '' }}',
      email: '{{ $loggedUser ? $loggedUser->email : '' }}',
      name: '{{ $loggedUser ? $loggedUser->fname . ' ' . substr($loggedUser->lname, 0, 1) : '' }}'
    };
  </script>
  <script>
    var doc = {!! $doc->toJSON() !!};
    @if($showAnnotationThanks)
    $.showAnnotationThanks = true;
    @else
    $.showAnnotationThanks = false;
    @endif
  </script>
@stop
