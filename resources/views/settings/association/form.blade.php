@extends('settings/settings')

<?php $edit = isset($data); ?>

@section('settings_content')
<h1>Verein {{ $edit ? '&auml;ndern' : 'hinzuf&uuml;gen' }}</h1>
@if(count($errors->all()) > 0)
    <div class="alert alert-danger">
        <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
        <ul>
            @foreach ($errors->all(('<li>:message</li>')) as $message)
                {!!$message!!}
            @endforeach
        </ul>
    </div>
@endif
{!! Form::open(array('class' => 'form-horizontal', 'role' => 'form')) !!}
    @if(Auth::user()->isAdmin())
    <div class="form-group">
        {!! Form::Label('id', 'Standnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('id', (isset($data) ? $data['id'] : ''), array('class' => 'form-control col-md3', 'readonly' => !Auth::user()->isAdmin())) !!}
        </div>
    </div>
    @endif
    <div class="form-group">
        {!! Form::Label('name', 'Bezeichnung', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('name', (isset($data) ? $data['name'] : ''), array('class' => 'form-control col-md3')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('email', 'E-Mail', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('email', ($edit ? $data['email'] : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('wuser_username', 'Verwalter', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('wuser_username', $users, ($edit ? $data['wuser_username'] : 'none'), array('class' => 'form-control col-md3', 'readonly' => !Auth::user()->isAdmin())) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop
