@extends('settings/settings')

@section('settings_content')
<h1>Bewerber l&ouml;schen</h1>
{!! Form::open() !!}
    <div class="form-group">
	Sind Sie sicher, dass sie <strong>den Bewerber "{{ $applicant->id}}"
            l&ouml;schen</strong> m&ouml;chten?<br>
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}
@stop
