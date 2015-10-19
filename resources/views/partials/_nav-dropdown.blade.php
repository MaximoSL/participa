<div class="btn-group">
  <button type="button" class="btn btn-gray dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    @if($loggedUser)
      <i class="icon icon-user"></i> &nbsp; {{ $loggedUser->name }} <span class="caret"></span>
    @else
      Menú <span class="caret"></span>
    @endif
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
    <li class="link-about">
      <a href="{{ route('about') }}" target="_self">
        {{ trans('messages.about') }}
      </a>
    </li>
    @if(!$loggedUser)
      <li class="link-login">
        <a href="{{ route('auth.login') }}" target="_self">
          {{ trans('messages.login') }}
        </a>
      </li>
      <li class="link-signup">
        <a href="{{ route('auth.signup') }}" target="_self">
          {{ trans('messages.signup') }}
        </a>
      </li>
    @else
      @if($loggedUser->hasRole('Independent Sponsor') || $loggedUser->hasRole('Admin'))
        <li class="link-settings">
          <a href="{{ route('documents') }}" target="_self">
            {{ trans('messages.mydocs') }}
          </a>
        </li>
      @endif
      <li class="link-settings">
        <a href="{{ route('user.account') }}" target="_self">
          {{ trans('messages.accountsettings') }}
        </a>
      </li>
      @if($loggedUser->hasRole('Admin'))
        <li>
          <a href="{{ route('dashboard') }}" target="_self">
            {{ trans('messages.admin') }}
          </a>
        </li>
      @endif
      <li role="separator" class="divider"></li>
      <li class="link-logout">
        <a href="{{ route('auth.logout') }}" target="_self">
          {{ trans('messages.logout') }}
        </a>
      </li>
    @endif
  </ul>
</div>
