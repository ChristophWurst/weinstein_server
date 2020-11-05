@extends('settings/settings')

@section('settings_content')

<h1>{!! $data->id !!} {!! $data->name !!}</h1>
<a href="{!! route('settings.associations/edit', array('association' => $data->id)) !!}" type="button" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> bearbeiten</a>
<h2>Verein</h2>
<p>
    <strong>Standnummer:</strong> {!! $data->id !!}
</p>
<p>
    <strong>Bezeichnung:</strong> {!! $data->name !!}
</p>
<p>
    <strong>Verwalter:</strong>
    @if ($data->wuser_username)
    {!! link_to_route('settings.user/show', $data->wuser_username, array('user' => $data->wuser_username)) !!}
    @else
    -
    @endif
</p>
<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Betriebe
                <span class="badge pull-right">{{ $data->applicants->count() }}</span></h3>
            </div>
            <ul class="list-group">
                @foreach($data->applicants as $applicant)
                <li class="list-group-item">
                    {!! link_to_route('settings.applicant/show', 
                                  $applicant->id . ' - ' .$applicant->label . ' ' . $applicant->lastname,
                                  array('applicant' => $applicant->id))
                    !!}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@stop