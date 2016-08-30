@extends('settings/settings')

<?php $edit = isset($applicant); ?>

@section('settings_content')
@if ($edit)
<h1>Betrieb "{{ $applicant->label }}" &auml;ndern</h1>
@else
<h1>Betrieb hinzuf&uuml;gen</h1>
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
    @if($edit || Auth::user()->admin)
    <div class="form-group">
        {!! Form::Label('id', 'Betriebsnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            <?php
			$options = [
				'class' => 'form-control'
			];
			if ($edit) {
				$options['readonly'] = 'readonly';
			}
			?>
            {!! Form::text('id', ($edit ? $applicant->id : ''), $options) !!}
        </div>
    </div>
    @endif
    @if(Auth::user()->admin)
    <div class="form-group">
        {!! Form::Label('association_id', 'Verein', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('association_id', $associations, ($edit ? $applicant->association_id : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('wuser_username', 'Benutzer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::select('wuser_username', $users, ($edit ? $applicant->wuser_username : 'none'), array('class' => 'form-control')) !!}
        </div>
    </div>
    @endif
    <div class="form-group">
        {!! Form::Label('label', 'Bezeichnung', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('label', ($edit ? $applicant->label : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('title', 'Titel', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('title', ($edit ? $applicant->title : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('firstname', 'Vorname', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('firstname', ($edit ? $applicant->firstname : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('lastnmae', 'Nachname', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('lastname', ($edit ? $applicant->lastname : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('phone', 'Telefonnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('phone', ($edit ? $applicant->phone : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('fax', 'Faxnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('fax', ($edit ? $applicant->fax : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('mobile', 'Mobilnummer', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('mobile', ($edit ? $applicant->mobile : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('email', 'E-Mail', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('email', ($edit ? $applicant->email : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('web', 'Webseite', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            {!! Form::text('web', ($edit ? $applicant->web : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('street', 'Straße/Nr', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-8 col-md-2">
            {!! Form::text('street', ($edit ? $applicant->address->street : ''), array('class' => 'form-control')) !!}
        </div>
        <div class="col-sm-2 col-md-1">
            {!! Form::text('nr', ($edit ? $applicant->address->nr : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::Label('zipcode', 'PLZ/Ort', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-2 col-md-1">
            {!! Form::text('zipcode', ($edit ? $applicant->address->zipcode : ''), array('class' => 'form-control')) !!}
        </div>
        <div class="col-sm-8 col-md-2">
            {!! Form::text('city', ($edit ? $applicant->address->city : ''), array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop