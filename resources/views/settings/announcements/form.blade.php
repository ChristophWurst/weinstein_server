@extends('settings/settings')

<?php $edit = isset($data); ?>

@section('settings_content')
    @if (Session::has('announcement_sent'))
        <div class="alert alert-success">
            <strong>Mitteilung werden versendet</strong><br>
			<?php
			list ($cnt) = Session::get('announcement_sent');
			?>
            Mitteilung wird an {{ $cnt  }} Betriebe versandt. Dieser Vorgang kann einige Minuten dauern.
        </div>
    @endif
    <h1>Mitteilung an Betriebe versenden</h1>
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
    <div class="form-group">
        {!! Form::Label('subject', 'Betreff', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('subject', '', array('class' => 'form-control col-md3', 'required' => true)) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('text', 'Nachricht', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::textarea('text', '', array('class' => 'form-control col-md3 no-magic', 'required' => true)) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Senden', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
    {!! Form::close() !!}
@stop
