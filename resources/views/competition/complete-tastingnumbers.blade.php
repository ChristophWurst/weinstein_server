@extends('default')

@section('content')
<h1>Kostnummernzuweisung abschlie&szlig;en</h1>
{!! Form::open() !!}
    Sind Sie sicher, dass sie die Zuweisung der {!! $tasting !!}. Kostnummern abschlie&szlig;en wollen?<br>
    Danach ist keine Zuweisung bzw. Aufhebung einer Zuweisung mehr möglich.
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop