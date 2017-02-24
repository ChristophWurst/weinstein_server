@extends('default')

@section('content')
<h1>Kostnummern zur&uuml;cksetzen</h1>
{!! Form::open() !!}
<div class="form-group">
		Sind Sie sicher, dass sie <strong>alle Kostnummern</strong> zur&uu&uuml;cksetzen m&ouml;chten?<br>
        {!! Form::submit('Ja', array('name' => 'reset', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'reset', 'class' => 'btn btn-default')) !!}
</div>
{!! Form::close() !!}

@stop