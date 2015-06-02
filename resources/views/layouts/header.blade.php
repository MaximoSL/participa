<?php
  $activeGroupId = Session::get('activeGroupId');
?>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarMainCollapse">
        <span class="sr-only">Interruptor de Navegación</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ route('home') }}">
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
            @if($loggedUser)
              @if($loggedUser->hasRole('Independent Sponsor') || $loggedUser->groups()->exists())
                <li class="link-settings"><a href="{{ route('documents') }}" target="_self">{{ trans('messages.mydocs') }}</a>
              @endif
              <li class="link-settings"><a href="{{ route('editUser', $loggedUser->id) }}" target="_self">{{ trans('messages.accountsettings') }}</a></li>
              <li><a href="{{ route('editNotifications', $loggedUser->id) }}" target="_self">{{ trans('messages.notifsettings') }}</a></li>
              <li class="link-settings"><a href="{{ route('groups') }}" target="_self">{{ trans('messages.groupmanagement') }}</a></li>
              @if($loggedUser->hasRole('Admin'))
                <li><a href="{{ route('dashboard') }}" target="_self">{{ trans('messages.admin') }}</a></li>
              @endif
              <?php $userGroups = $loggedUser->groups(); ?>
              <?php if ($userGroups->count() > 0): ?>
                <li class="dropdown-submenu pull-left">
                  <a class="dropdown-trigger" href="#" data-toggle="dropdown">{{ trans('messages.useas') }}</a>
                  <ul class="dropdown-menu" role="menu">
                    <?php if ($activeGroupId !== 0): ?>
                      <li class="link-settings"><a href="./groups/active/0" target="_self">{{ $loggedUser->fname }} {{ $loggedUser->lname }}</a></li>
                    <?php endif; ?>
                    <li class="divider"></li>
                    <?php foreach ($userGroups->get() as $group): ?>
                      <li class="link-settings"><a href="./groups/active/{{ $group->id }}" target="_self">{{ $group->getDisplayName() }} {{ $group->id == $activeGroupId ? '(active)' : '' }}</a></li>
                    <?php endforeach;?>
                  </ul>
                </li>
              <?php endif; ?>
              <li class="link-logout"><a href="{{ route('logout') }}" target="_self">{{ trans('messages.logout') }}</a></li>
            @else
              <li class="link-login"><a href="{{ route('auth.login') }}" target="_self">{{ trans('messages.login') }}</a></li>
              <li class="link-signup"><a href="{{ route('auth.signup') }}" target="_self">{{ trans('messages.signup') }}</a></li>
            @endif
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
