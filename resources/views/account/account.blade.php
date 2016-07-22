@extends('default')

@section('content')
<h1>Account</h1>
<p>Sie sind angemledet als <strong>{{ Auth::user()->username }}.</strong></p>
<a class="btn btn-default"
   role="button"
   href="{!! route('settings.users/edit', array('username' => Auth::user()->username)) !!}">
    Anmeldedaten &auml;ndern
</a>
<a class="btn btn-default"
   role="button"
   href="{!!route('logout')!!}">
    abmelden
</a>
@stop