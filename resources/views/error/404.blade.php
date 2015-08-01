@extends('layouts.main')

@section('content')
<main class="inner-page pagina" role="main">
    <div class="container">
        <div class="col-sm-12" style="text-align: center; margin: 34px auto; padding: 0 !important;">
          <h1 style="font-size: 8em; font-weight: 700;">404</h1>
          <p>No se encontró la página.</p>
          <a href="{{ route('home') }}">Regresar al inicio</a>
        </div>
    </div>
</main>
@stop
