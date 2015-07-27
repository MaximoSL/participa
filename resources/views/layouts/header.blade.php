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
        <li><a href="http://www.gob.mx/participa">Participa</a></li>
        <li>
          <a href="http://www.gob.mx/busqueda">
            <i class="icon-search"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
