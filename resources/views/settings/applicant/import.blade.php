@extends('settings/settings')

@section('settings_content')
<h1>Daten importieren</h1>
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
{!! Form::open(array('files' => true)) !!}
    <div class="form-group">
        {!! Form::Label('xlsfile', 'Excel Datei') . Form::file('xlsfile') !!}
        {!! Form::submit('Importieren', array('class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}
@stop