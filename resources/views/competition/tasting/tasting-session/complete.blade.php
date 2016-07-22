@extends('competition/tasting/tasting-session/tasting-session')

@section('main_content')
<h1>Kostsitzung abschlie&szlig;en</h1>
{!! Form::open() !!}
    Sind Sie sicher, dass sie die <strong>{{ $data['nr']  }}. Sitzung</strong> abschlie&szlig;en wollen?<br>
    Danach sind weder Verkostungen, noch Nachverkostungen mehr m&ouml;glich.
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop