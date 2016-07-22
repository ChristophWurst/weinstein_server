@extends ('default')

@section ('content')
<h1>Kostsitzungsprotokolle</h1>
<h2>1. Verkostung</h2>
@foreach ($tasting_sessions1 as $ts)
<a class="btn btn-default"
   type="button"
   href="{!! route('tasting.sessions/protocol', array('tastingsession' => $ts->id)) !!}">
    <span class="glyphicon glyphicon-export"></span>
   {{ $ts->nr }}. Sitzung
</a>
@endforeach
<h2>2. Verkostung</h2>
@foreach ($tasting_sessions2 as $ts)
<a class="btn btn-default"
   type="button"
   href="{!! route('tasting.sessions/protocol', array('tastingsession' => $ts->id)) !!}">
    <span class="glyphicon glyphicon-export"></span>
   {{ $ts->nr }}. Sitzung
</a>
@endforeach
@stop