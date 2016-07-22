@extends('settings/settings')

@section('settings_content')

<h1>{!! $data->username !!}</h1>
<a href="{!! route('settings.users/edit', array('username' => $data->username)) !!}" type="button" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span> bearbeiten</a>
<p></p>
<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Verwaltete Vereine
                <span class="badge pull-right">{{ $data->associations->count() }}</h3>
            </div>
            <ul class="list-group">
                @foreach($data->associations as $association)
                <li class="list-group-item">
                    {!! link_to_route('settings.association/show', 
                                $association->id . ' - ' .$association->name,
                                array('id' => $association->id)) 
                    !!}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Verwaltete Betriebe
                <span class="badge pull-right">{{ $data->applicants->count() }}</span></h3>
            </div>
            <div class="panel-body bg-info">
                <small>Hier werden nur direkt zugewiesene Betriebe gelistet. Eventuell werden mehr Betriebe indirekt durch die Verwaltung eines Vereins verwaltet</small>
            </div>
            <ul class="list-group">
                @foreach($data->applicants as $applicant)
                <li class="list-group-item">
                    {!! link_to_route('settings.applicant/show', 
                                  $applicant->id . ' - ' .$applicant->label . ' ' . $applicant->lastname,
                                  array('id' => $applicant->id)) 
                    !!}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

@stop