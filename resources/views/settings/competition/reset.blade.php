@extends('default')

@section('content')
<h1>Bewerb zur&uuml;cksetzen</h1>
{!! Form::open() !!}
    Sind Sie sicher, dass sie den Bewerb zu&uuml;cksetzen wollen?<br>
    Alle Daten (Weine, Kostnummern, Bewertungen usw.) werden gel&ouml;scht. Betrieben,
    Vereine, Sorten und Benutzer werden nicht gel&ouml;scht.
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'reset', 'class' => 'btn btn-danger')) !!}
        {!! Form::submit('Nein', array('name' => 'reset', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop