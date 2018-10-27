@extends('default')

@section('content')
<h1>Account</h1>
<p>Sie sind angemeldet als <strong>{{ Auth::user()->username }}.</strong></p>

@if(Session::has('first-login'))
    <div class="alert alert-info">
        <strong>Hinweis:</strong> Sie haben sich zum ersten mal angemeldet. Sie sollten nun Ihr Passwort ändern.
    </div>
	<?php
	Session::forget('successful');
	?>
@endif

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
