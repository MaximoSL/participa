<!DOCTYPE html>
<html xmlns:ng="http://angularjs.org" id="ng-app" ng-app="madisonApp" ng-controller="AppController" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>

		<meta name="env" content="{{ app('env') }}">
    <meta name="token" content="{{ csrf_token() }}">

		<!-- Mobile Optimization -->
		<meta name="HandheldFriendly" content="True" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimum-scale=1.0">
		<meta name="format-detection" content="telephone=no" />
		<meta http-equiv="cleartype" content="on" />

		@if(isset($page_title))
		<title>{{ $page_title }}</title>
		@else
		<title ng-bind="pageTitle">Madison</title>
		@endif

		<!--[if lt IE 9]>
		<script>
          document.createElement('ng-include');
          document.createElement('ng-pluralize');
          document.createElement('ng-view');

          // Optionally these for CSS
          document.createElement('ng:include');
          document.createElement('ng:pluralize');
          document.createElement('ng:view');
		</script>
		<![endif]-->
		@include('layouts.socials')
		@include('layouts.assets')
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
		<script type="text/javascript">
			var _sf_startpt=(new Date()).getTime();
      var _basePath = '{{ config('app.base_name') }}';
			var _baseUrl = '{{ route('home') }}';
		</script>
	</head>
	<body>
		@include('layouts.analytics')
		<!--[if lt IE 8]>
			<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
		<div growl></div>
		@include('layouts.header')
		@include('errors')
		@include('message')
		@include('success')
		<div profile-completion-message></div>
		<main>
			@yield('content')
		</main>
		@include('layouts.footer')
		<!-- Scripts -->
		<script src="{{ elixir('dist/js/libs.js') }}"></script>
		<script src="{{ elixir('dist/js/app.js') }}"></script>
		<script type="text/javascript">
			ZeroClipboard.config( { swfPath: '{{ asset_url('swf/ZeroClipboard.swf') }}' } );
		</script>
		@yield('js')
		<script type="text/javascript">
			var _sf_async_config = { uid: 43659, domain: 'www.gob.mx', useCanonical: true };
			(function() {
			  function loadChartbeat() {
				window._sf_endpt = (new Date()).getTime();
				var e = document.createElement('script');
				e.setAttribute('language', 'javascript');
				e.setAttribute('type', 'text/javascript');
				e.setAttribute('src','//static.chartbeat.com/js/chartbeat.js');
				document.body.appendChild(e);
			  };
			  var oldonload = window.onload;
			  window.onload = (typeof window.onload != 'function') ?
				loadChartbeat : function() { oldonload(); loadChartbeat(); };
			})();
		</script>
	</body>
</html>
