@extends('default')

@section('content')
<h1>Auswahl abschlie&szlig;en</h1>
{!! Form::open() !!}
    Sind Sie sicher, dass Sie die Auswahl der auszuschenkenden Weine abschlie&szlig;en wollen?<br>
    Danach sind keine &Auml;nderungen mehr m&ouml;glich.
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop