@extends('default')

@section('jumbotron')
<div class="jumbotron">
    <div class="container text-center">
        <img height="120" width="120" src="{!! asset('img/ws-logo.svg') !!}">
        <h1>Weinstein</h1>
        @if (!Auth::check())
        <p>
            Willkommen!
        </p>
        <p>
            Bitte melden Sie sich an
        </p>
        <p>
            <a href="{!! route('login') !!}" class="btn btn-primary btn-lg" role="button">Anmelden</a>
        </p>
        @else
        Entwickelt von Christoph Wurst, 2013 - 2017
        @endif
    </div>
</div>
@stop
