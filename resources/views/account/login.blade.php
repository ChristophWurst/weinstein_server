@extends('default')

@section('content')
	{!! Form::open(array('url' => route('login'), 'class' => 'form-signin')) !!}
	<h1 class="form-signin-heading">Anmelden</h1>
	@if(!Session::get('successful', true))
            <div class="alert alert-danger">
                <strong>Fehler!</strong> Benutzername/Passwort inkorrekt
            </div>
            <?php
				Session::forget('successful');
			?>
        @endif
	<input type="text" name="username" class="form-control" placeholder="Benutzername" required autofocus>
	<input type="password" name="password" class="form-control" placeholder="Passwort" required>
	<button class="btn btn-default btn-block" type="submit">Anmelden</button>
	{!! Form::close() !!}
@stop