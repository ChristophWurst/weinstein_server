@extends('default')

@section('content')
<h1>Kostnummernzuweisung aufheben</h1>
{!! Form::open() !!}
    Sind Sie sicher, dass sie die Zuweisung von <strong>Dateinummer 
    {{ $data->wine->nr }}</strong> zu <strong>Kostnummer {{ $data->nr }}</strong>
    aufheben wollen?
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop