<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarMainCollapse">
        <span class="sr-only">Interruptor de Navegación</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ url('http://www.gob.mx/') }}">
        <img src="{{ url('svg/gob-mx-logo.svg') }}" width="75" height="23" alt="gob.mx">
      </a>
    </div>
    <div class="collapse navbar-collapse" id="navbarMainCollapse">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="http://www.gob.mx/tramites">{{ trans('messages.services') }}</a></li>
        <li><a href="http://www.gob.mx/presidencia">{{ trans('messages.government') }}</a></li>
        <li class="dropdown">
          <a class="dropdown-trigger" href="#" data-toggle="dropdown">{{ trans('messages.sitename') }}<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li class="link-about">
              <a href="{{ route('about') }}" target="_self">{{ trans('messages.about') }}</a>
            </li>
            @if(!$loggedUser)
              <li class="link-login"><a href="{{ route('auth.login') }}" target="_self">{{ trans('messages.login') }}</a></li>
              <li class="link-signup"><a href="{{ route('auth.signup') }}" target="_self">{{ trans('messages.signup') }}</a></li>
            @else
              @if($loggedUser->hasRole('Independent Sponsor') || $loggedUser->hasRole('Admin'))
                <li class="link-settings"><a href="{{ route('documents') }}" target="_self">{{ trans('messages.mydocs') }}</a>
              @endif
              <li class="link-settings"><a href="{{ route('user.account') }}" target="_self">{{ trans('messages.accountsettings') }}</a></li>
              @if($loggedUser->hasRole('Admin'))
                <li><a href="{{ route('dashboard') }}" target="_self">{{ trans('messages.admin') }}</a></li>
              @endif
              <li class="link-logout"><a href="{{ route('auth.logout') }}" target="_self">{{ trans('messages.logout') }}</a></li>
            @endif
          </ul>
        </li>
        <li>
          <a href="http://www.gob.mx/busqueda">
            <i class="icon-search"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
