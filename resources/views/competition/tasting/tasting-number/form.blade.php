@extends('default')

<?php $edit = isset($data); ?>

@section('content')
<h1>Kostnummer {!! $edit ? '&auml;ndern' : 'zuweisen' !!}</h1>
@if (count($errors->all()) > 0)
    <div class="alert alert-danger">
        <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
        <ul>
            @foreach ($errors->all('<li>:message</li>') as $message)
                {!! $message !!}
            @endforeach
        </ul>
    </div>
@endif
{!! Form::open(array('class' => 'form-horizontal', 'role' => 'form')) !!}
    <div class="form-group">
        {!! Form::Label('wine_nr', 'Dateinummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('wine_nr',
                        ($edit ? $data->wine->nr : ''),
                        array('class' => 'form-control', 'autofocus')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('nr', 'Kostnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('nr', ($edit ? $data->nr : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Zuweisen', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop