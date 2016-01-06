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

      @include('partials._secondary-nav', [
        'breadcrumbs' => [
          [
            'route' => 'docs.doc',
            'params' => ['slug' => $doc->slug],
            'label' => $doc->title
          ],
        ]
      ])

      <div class="row" ng-controller="ReaderController" ng-init="init({{ $doc->id }})">
        <div class="col-md-8">
          <div class="doc-head">
            <h1>{{ $doc->title }}</h1>
            @if(!$doc->is_opened)
              <p class="text-danger"><b>{{ $doc->statuses->first()->label }}</b></p>
            @endif
            <ul class="list-unstyled">
              <li>
                <small>@{{ 'POSTED' | translate }}: @{{ doc.created_at | date: 'longDate' }}, @{{ doc.created_at | date: 'HH:mm:ss' }}</small>
              </li>
              <li>
                <small>@{{ 'UPDATED' | translate }}: @{{ doc.updated_at | date: 'longDate' }}, @{{ doc.updated_at | date: 'HH:mm:ss' }}</small>
              </li>
              <li ng-repeat="date in doc.dates">
                <small>@{{ date.label }}: @{{ date.date | parseDateIso | date: 'longDate' }}, @{{ date.date | parseDateIso | date: 'HH:mm:ss' }}</small>
              </li>
            </ul>
            <div class="doc-extract" ng-if="introtext">
              <div class="markdown" data-ng-bind-html="introtext"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <ul class="nav nav-tabs" role="tablist" tourtip="@{{ step_messages.step_3 }}" tourtip-step="3" tourtip-next-label="Siguiente">
            <li ng-class="{'active':secondtab == false}"><a href="#tab-activity" target="_self" role="tab" data-toggle="tab">{{ trans('messages.project') }}</a></li>
            <li ng-class="{'active':secondtab == true}"><a href="#tab-discussion" target="_self" role="tab" data-toggle="tab">{{ trans('messages.discussion') }}</a></li>
            <a href="{{ route('docs.feed', $doc->slug) }}" class="rss-link" target="_self"><img src="{{ asset_url('img/rss-fade.png') }}" class="rss-icon" alt="RSS Icon"></a>
          </ul>

          <div class="tab-content">
            <div id="tab-activity" ng-class="{'active':secondtab == false}" class="tab-pane">
              <div id="content" class="@if($loggedUser) logged_in @endif" tourtip="@{{ step_messages.step_2 }}" tourtip-step="2" tourtip-next-label="Siguiente">

                <div class="row">
                  <ul class="col-sm-12 nav nav-pills" role="tablist">
                    <li ng-class="{'active':inlinediff == true}" ng-click="inlinediff = true" class="pull-right" id="inline-diff-layout-toggle"><a href="#" target="_self">{{ trans('messages.leyes-layout-inline-diff') }}</a></li>
                    <li ng-class="{'active':inlinediff == false}" ng-click="inlinediff = false"  class="pull-right" id="side-diff-layout-toggle"><a href="#" target="_self">{{ trans('messages.leyes-layout-side-diff') }}</a></li>
                  </ul>
                </div>

                {{-- <div class="tab-content">

                  <div id="tab-side-diff" ng-class="{'active':inlinediff == false}" class="tab-pane">
                    <div id="doc_content" class="doc-content-main">
                      {!! $doc->formatted_content !!}
                    </div>
                  </div>

                  <div id="tab-inline-diff" ng-class="{'active':inlinediff == true}" class="tab-pane">
                    <div id="doc_content" class="doc-content-main">
                      {!! $doc->formatted_content !!}
                    </div>
                  </div>
                </div> --}}

                <div id="doc_content" class="doc-content-main">
                  {!! $doc->formatted_content !!}
                </div>
              </div>
            </div>

            <div id="tab-discussion" ng-class="{'active': secondtab == true}" class="tab-pane">
              <div class="doc-forum" ng-controller="CommentController" ng-init="init({{ $doc->id }})">
                @include('doc.reader.comments')
              </div>
            </div>
          </div>

        </div>
        <div class="col-md-4">
          <div class="doc-content-sidebar">
            <div class="sidebar-unit">
              <h4>{{ trans('messages.howtoparticipate') }}</h4>
              <hr class="red">
              <ol>
                <li>{{ trans('messages.readpolicy') }}</li>
                <li>{{ trans('messages.signupnaddvoice') }}</li>
                <li>{{ trans('messages.anncommsuppopp') }}</li>
              </ol>
              <img src="{{ asset_url('img/como-comentar.gif') }}" class="how-to-annotate-img img-responsive" alt="{{ trans('messages.howtoparticipate') }}" />
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
