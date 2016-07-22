@extends('settings/settings')

<?php $edit = isset($data); ?>

@section('settings_content')
<h1>Benutzer {!! $edit ? 'bearbeiten' : 'hinzuf&uuml;gen' !!}</h1>
@if(count($errors->all()) > 0)
<div class="alert alert-danger">
    <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
    <ul>
        @foreach ($errors->all(('<li>:message</li>')) as $message)
        {!! $message !!}
        @endforeach
    </ul>
</div>
@endif
{!! Form::open(array('class' => 'form-horizontal', 'role' => 'form')) !!}
    <div class="form-group">
        {!! Form::Label('username', 'Benutzername', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-2">
            {!! Form::text('username', ($edit ? $data['username'] : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        @if($edit)
        {!! Form::Label('password', 'Passwort (Ohne Eingabe bleibt das alte Passwort erhalten)', array('class' => 'col-sm-2 control-label')) !!}
        @else
        {!! Form::Label('password', 'Passwort', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        @endif
        <div class="col-sm-10 col-md-2">
            {!! Form::text('password', '', array('class' => 'form-control')) !!}
        </div>
    </div>
    @if((Auth::user()->admin) && ($edit && (Auth::user()->username != $data['username'])))
        <div class="form-group">
            {!! Form::Label('admin', 'Administrator', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
            <div class="col-sm-10 col-md-2">
                {!! Form::checkbox('admin', 'true', ($edit ? $data['admin'] : false), array('class' => 'form-control')) !!}
            </div>
        </div>

    @endif
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            @if($edit)
            {!! Form::submit('&Auml;nderungen speichern', array('class' => 'btn btn-default')) !!}
            @else
            {!! Form::submit('Hinzuf&uuml;gen', array('class' => 'btn btn-default')) !!}
            @endif
        </div>
    </div>
{!! Form::close() !!}
@stop