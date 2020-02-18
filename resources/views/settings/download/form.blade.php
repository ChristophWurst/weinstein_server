@extends('settings/settings')

<?php $edit = isset($data); ?>

@section('settings_content')
    <h1>Datei hochladen</h1>
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
    {!! Form::open(array('class' => 'form-horizontal', 'role' => 'form', 'files' => true)) !!}
    <div class="form-group">
        {!! Form::Label('name', 'Name (frei lassen, um Originalname zu übernehmen)', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('name', '', array('class' => 'form-control col-md3')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('file', 'Datei', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::file('file') !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
    {!! Form::close() !!}
@stop
