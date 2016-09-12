@extends('default')

<?php $edit = isset($wine); ?>

@section('content')
<h1>Wein {{ $edit ? '&auml;ndern' : 'hinzuf&uuml;gen' }}</h1>
@if($success)
    <div class="alert alert-success">
        <strong>Wein gespeichert.</strong> Der Wein wurde erfolgreich gespeichert. Sie können nun weitere Weine anlegen.
		<a href="{!! route('enrollment.wines', array('competition' => $competition->id)) !!}">Zurück zur Übersicht</a>
    </div>
@endif
@if(count($errors->all()) > 0)
    <div class="alert alert-danger">
        <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
        <ul>
            @foreach ($errors->all(('<li>:message</li>')) as $message)
            {!! $message !!}
            @endforeach
        </ul>
    </div>
@endif
{!! Form::open(array('class' => 'form-horizontal', 'role' => 'form')) !!}
    @if($show_nr)
    <div class="form-group">
        {!! Form::Label('nr', 'Dateinummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('nr',
                    ($edit ? $wine['nr'] : $id),
                    array('class' => 'form-control')) !!}
        </div>
    </div>
    @endif
    <div class="form-group">
        {!! Form::Label('applicant_id', 'Betrieb', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('applicant_id',
                    $applicants,
                    ($edit ? $wine['applicant_id'] : null),
                    array('class' => 'form-control col-md3', 'autofocus')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('label', 'Marke', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-2">
            {!! Form::text('label', ($edit ? $wine['label'] : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('winesort_id', 'Sorte', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('winesort_id',
                    $winesorts,
                    ($edit ? $wine['winesort_id'] : null),
                    array('class' => 'form-control col-md3')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('winequality_id', 'Qualit&auml;tsstufe', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('winequality_id',
                    $winequalities,
                    ($edit ? $wine['winequality_id'] : 2),
                    array('class' => 'form-control col-md3')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('vintage', 'Jahrgang', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('vintage', ($edit ? $wine['vintage'] : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('alcohol', 'Alkohol', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('alcohol', ($edit ? str_replace(".", ",", $wine['alcohol']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('alcoholtot', 'Alkohol gesamt', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('alcoholtot', ($edit ? str_replace(".", ",", $wine['alcoholtot']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('sugar', 'Zucker', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('sugar', ($edit ? str_replace(".", ",", $wine['sugar']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('approvalnr', 'Pr&uuml;fnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('approvalnr', ($edit ? str_replace(".", ",", $wine['approvalnr']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop

@section ('script')
function select_matcher(term, text) {
    return text.toUpperCase().indexOf(term.toUpperCase())==0;
}

$("#applicant_id").select2({
    matcher: select_matcher
});
$("#association_id").select2({
    matcher: select_matcher
});
$("#winesort_id").select2({
    matcher: select_matcher
});
$("#winequality_id").select2({
    matcher: select_matcher
});
@stop