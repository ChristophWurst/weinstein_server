@extends('default')

@section('content')
<h1>Wein l&ouml;schen</h1>
{!! Form::open() !!}
    <div class="form-group">
	Sind Sie sicher, dass sie <strong>den Wein {{ $wine->nr ? '(Dateinummer ' . $wine->nr . ')' : '' }}
            l&ouml;schen</strong> m&ouml;chten?<br>
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop