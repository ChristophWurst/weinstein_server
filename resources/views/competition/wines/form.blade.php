@extends('default')

<?php $edit = isset($wine); ?>

@section('content')
<h1>Wein {{ $edit ? '&auml;ndern' : 'hinzufügen' }}</h1>
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
        {!! Form::Label('alcohol', 'Vorhandener Alkohol [%]', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('alcohol', ($edit ? str_replace(".", ",", $wine['alcohol']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('acidity', 'Säure [g/l]', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::text('acidity', ($edit ? str_replace(".", ",", $wine['acidity']) : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('sugar', 'Zucker [g/l]', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
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
        {!! Form::Label('approvalnr', 'Urkunde für KdB erwünscht', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-1">
            {!! Form::checkbox('kdb_certificate', 'true', false, array('class' => 'custom-control-input')) !!}
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
function oldMatcher(matcher) {
		function wrappedMatcher(params, data) {
			var match = $.extend(true, {}, data);

			if (params.term == null || $.trim(params.term) === '') {
				return match;
			}

			if (data.children) {
				for (var c = data.children.length - 1; c >= 0; c--) {
					var child = data.children[c];

					// Check if the child object matches
					// The old matcher returned a boolean true or false
					var doesMatch = matcher(params.term, child.text, child);

					// If the child didn't match, pop it off
					if (!doesMatch) {
						match.children.splice(c, 1);
					}
				}

				if (match.children.length > 0) {
					return match;
				}
			}

			if (matcher(params.term, data.text, data)) {
				return match;
			}

			return null;
		}

		return wrappedMatcher;
}

function select_matcher(term, text) {
    return text.toUpperCase().indexOf(term.toUpperCase())==0;
}

var select2_open;
// open select2 dropdown on focus
$(document).on('focus', '.select2-selection--single', function(e) {
    select2_open = $(this).parent().parent().siblings('select');
    select2_open.select2('open');
});

// fix for ie11
if (/rv:11.0/i.test(navigator.userAgent)) {
    $(document).on('blur', '.select2-search__field', function (e) {
        select2_open.select2('close');
    });
}

$("#applicant_id").select2({
    matcher: oldMatcher(select_matcher)
});
$("#association_id").select2({
    matcher: oldMatcher(select_matcher)
});
$("#winesort_id").select2({
    matcher: oldMatcher(select_matcher)
});
$("#winequality_id").select2({
    matcher: oldMatcher(select_matcher)
});
@stop
