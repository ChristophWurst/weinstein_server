@extends('default')

@section('content')
<div class="jumbotron">
    <div class="container">
        <h1>Interner Fehler ({{ isset($code) ? $code : 'unbekannt' }})</h1>
        <p>Ein unerwarteter Fehler trat auf und die Applikation musste beendet werden.<br>
        Falls der Fehler erneut auftritt, informieren Sie bitte den Administrator!</p>
        {!! link_to_route('start', 'zur Startseite', null, array('class' => 'btn btn-default')) !!}
    </div>
</div>
@stop
