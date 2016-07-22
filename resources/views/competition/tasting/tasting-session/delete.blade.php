@extends('competition/tasting/tasting-session/tasting-session')

@section('main_content')
<h1>Kostsitzung l&ouml;schen</h1>
{!! Form::open() !!}
Sind Sie sicher, dass sie die <strong>{{ $data['nr']  }}. Sitzung</strong> l&ouml;schen wollen?
    <div class="form-group">
        {!! Form::submit('Ja', array('name' => 'del', 'class' => 'btn btn-default')) !!}
        {!! Form::submit('Nein', array('name' => 'del', 'class' => 'btn btn-default')) !!}
    </div>
{!! Form::close() !!}

@stop