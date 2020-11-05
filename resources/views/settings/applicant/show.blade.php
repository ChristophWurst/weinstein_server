@extends('settings/settings')

@section('settings_content')

<h1>{!! $data->label !!} {!! $data->lastname !!} - {!! $data->address->city !!}</h1>
<p>
    <a href="{!! route('settings.applicants/edit', array('applicant' => $data->id)) !!}"
    accesskey=""type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-edit"></span>
        bearbeiten
    </a>
    @can('delete-applicant', $data)
    <a href="{!! route('settings.applicants/delete', array('applicant' => $data->id)) !!}"
       accesskey=""type="button" class="btn btn-warning">
        <span class="glyphicon glyphicon-remove"></span>
        l&ouml;schen
    </a>
    @endcan
</p>
<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Betrieb</h2>
            </div>
            <div class="panel-body">
                <p>
                    <strong>Betriebsnummer:</strong>
                    {!! $data->id !!}
                </p>
                <p>
                    <strong>Bezeichnung:</strong>
                    {!! $data->label !!}
                </p>
                <p>
                    <strong>Verwalter:</strong>
                    @if ($data->wuser_username)
                    {!! link_to_route('settings.user/show', $data->wuser_username, array('user' => $data->wuser_username)) !!}
                    @else
                    -
                    @endif
                </p>
                <p>
                    <strong>Verein:</strong> {!! link_to_route('settings.association/show', $data->association->name, array('association' => $data->association->id)) !!}
                </p>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Inhaber</h2>
            </div>
            <div class="panel-body">
                <p>
                    {!! $data->title !!} {!! $data->firstname !!} {!! $data->lastname !!}
                </p>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Kontakt</h2>
            </div>
            <div class="panel-body">
                <p>
                    <strong>Telefon:</strong>
                    {!! $data->phone !!}
                </p>
                <p>
                    <strong>Fax:</strong>
                    {!! $data->fax !!}
                </p>
                <p>
                    <strong>Mobil:</strong>
                    {!! $data->mobile !!}
                </p>
                <p>
                    <strong>E-Mail:</strong>
                    <a href="mailto:{!! $data->email !!}">{!! $data->email !!}</a>
                </p>
                <p>
                    <strong>Webseite:</strong>
                    <a target="_blank" href="http://{!! $data->web !!}">{!! $data->web !!}</a>
                </p>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Anschrift</h2>
            </div>
            <div class="panel-body">
                <p>
                    {!! $data->address->street !!} {!! $data->address->nr !!}<br>
                    {!! $data->address->zipcode!!} {!! $data->address->city !!}
                </p>
            </div>
        </div>
    </div>
</div>
@stop