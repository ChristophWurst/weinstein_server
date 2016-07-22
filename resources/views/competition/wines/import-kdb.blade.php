@extends('default')

@section('content')
<h1>KdB importieren</h1>
<div class="alert alert-info" role="alert">
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">
            &times;
        </span>
        <span class="sr-only">
            Close
        </span>
    </button>
    <div class="row">
        <div class="col-sm-8 col-md-6">
            <strong>Hinweis zum Aufbau der Datei</strong> <br>
            Bitte stellen Sie sicher, dass die Datei im folgenden Format vorliegt:
            <ul>
                <li>Spalte A enthält die Dateinummer</li>
            </ul>
        </div>
        <div class="col-sm-4 col-md-6">
            Beispiel:<br>
            <img src="{!! asset('img/example/kdb_sosi_chosen_import.png') !!}" alt="KdB importieren" />
        </div>
    </div>
</div>
@if(count($errors->all()) > 0)
<div class="alert alert-danger">
    <strong>Fehler!</strong> 
    Bitte korrigieren Sie folgende Eingaben:
    <ul>
        @foreach ($errors->all(('<li>:message</li>')) as $message)
        {!! $message !!}
        @endforeach
    </ul>
</div>
@endif
{!! Form::open(array('files' => true, 'class' => 'form-horizontal', 'role' => 'form')) !!}
    <div class="form-group">
        {!! Form::Label('xlsfile', '.xls oder .xlsx Datei', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::file('xlsfile') !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Importieren', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop