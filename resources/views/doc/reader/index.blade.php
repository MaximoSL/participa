@extends('layouts.main')

@section('content')
  
<!-- Begin Digital Analytix Tag 1.1302.13 -->
<script type="text/javascript">
	function udm_(e){var t="comScore=",n=document,r=n.cookie,i="",s="indexOf",o="substring",u="length",a=2048,f,l="&ns_",c="&",h,p,d,v,m=window,g=m.encodeURIComponent||escape;if(r[s](t)+1)for(d=0,p=r.split(";"),v=p[u];d<v;d++)h=p[d][s](t),h+1&&(i=c+unescape(p[d][o](h+t[u])));e+=l+"_t="+ +(new Date)+l+"c="+(n.characterSet||n.defaultCharset||"")+"&c8="+g(n.title)+i+"&c7="+g(n.URL)+"&c9="+g(n.referrer),e[u]>a&&e[s](c)>0&&(f=e[o](0,a-8).lastIndexOf(c),e=(e[o](0,f)+l+"cut="+g(e[o](f+1)))[o](0,a)),n.images?(h=new Image,m.ns_p||(ns_p=h),h.src=e):n.write("<","p","><",'img src="',e,'" height="1" width="1" alt="*"',"><","/p",">")};
	function uid_call(a, b){
		ui_c2 = 17183199; // your corporate c2 client value
		ui_ns_site = 'gobmx'; // your sites identifier
		window.b_ui_event = window.c_ui_event != null ? window.c_ui_event:"",window.c_ui_event = a;
		var ui_pixel_url = 'http://b.scorecardresearch.com/p?c1=2&c2='+ui_c2+'&ns_site='+ui_ns_site+'&name='+a+'&ns_type=hidden&type=hidden&ns_ui_type='+b;
		var b="comScore=",c=document,d=c.cookie,e="",f="indexOf",g="substring",h="length",i=2048,j,k="&ns_",l="&",m,n,o,p,q=window,r=q.encodeURIComponent||escape;if(d[f](b)+1)for(o=0,n=d.split(";"),p=n[h];o<p;o++)m=n[o][f](b),m+1&&(e=l+unescape(n[o][g](m+b[h])));ui_pixel_url+=k+"_t="+ +(new Date)+k+"c="+(c.characterSet||c.defaultCharset||"")+"&c8="+r(c.title)+e+"&c7="+r(c.URL)+"&c9="+r(c.referrer)+"&b_ui_event="+b_ui_event+"&c_ui_event="+c_ui_event,ui_pixel_url[h]>i&&ui_pixel_url[f](l)>0&&(j=ui_pixel_url[g](0,i-8).lastIndexOf(l),ui_pixel_url=(ui_pixel_url[g](0,j)+k+"cut="+r(ui_pixel_url[g](j+1)))[g](0,i)),c.images?(m=new Image,q.ns_p||(ns_p=m),m.src=ui_pixel_url):c.write("<p><img src='",ui_pixel_url,"' height='1' width='1' alt='*'></p>");
	}
	udm_('http://b.scorecardresearch.com/b?c1=2&c2=17183199&ns_site=gobmx&name=consulta.documento.{{ $doc->slug }}');
</script>
<noscript><p><img src="http://b.scorecardresearch.com/p?c1=2&amp;c2=17183199&amp;ns_site=gobmx&amp;name=consulta.documento.{{ $doc->slug }}" height="1" width="1" alt="*"></p></noscript> 
<!-- End Digital Analytix Tag 1.1302.13 -->
  
  
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
                <small>@{{ date.label }}: @{{ date.date | parseDate | date: 'longDate' }}, @{{ date.date | parseDate | date: 'HH:mm:ss' }}</small>
              </li>
            </ul>
            <div class="doc-extract" ng-if="introtext">
              <div class="markdown" data-ng-bind-html="introtext"></div>
            </div>
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
            <li ng-class="{'active':secondtab == false}"><a href="#tab-activity" target="_self" role="tab" data-toggle="tab">{{ trans('messages.bill') }}</a></li>
            <li ng-class="{'active':secondtab == true}"><a href="#tab-discussion" target="_self" role="tab" data-toggle="tab">{{ trans('messages.discussion') }}</a></li>
            <a href="{{ route('docs.feed', $doc->slug) }}" class="rss-link" target="_self"><img src="{{ asset_url('img/rss-fade.png') }}" class="rss-icon" alt="RSS Icon"></a>
          </ul>

          <div class="tab-content">
            <div id="tab-activity" ng-class="{'active':secondtab == false}" class="tab-pane">
              <div id="content" class="@if($loggedUser) logged_in @endif" tourtip="@{{ step_messages.step_2 }}" tourtip-step="2" tourtip-next-label="Siguiente">
                <div id="doc_content" class="doc-content-main" tourtip="@{{ step_messages.step_4 }}" tourtip-step="4" tourtip-next-label="Finalizar">
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
              <img src="{{ asset_url('img/como-comentar.gif') }}" class="how-to-annotate-img img-responsive" />
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
