<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
  </head>
  <body>
    <h2>{{ trans('messages.resetemailtitle') }}</h2>

    <div>
      {{ trans('messages.resetemailmessage') }} {{ route('password.reset').'/'.$token }}.
    </div>
  </body>
</html>
