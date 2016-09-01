@extends('settings/settings')

@section('settings_content')
<h1>Vereine</h1>
@if (Auth::user()->isAdmin())
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.associations/create') !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Verein hinzuf&uuml;gen
</a>
@else
<div class="alert alert-info" role="alert">
    <strong>Hinweis:</strong> Sie sehen nur Vereine, die Sie verwalten
</div>
@endif
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>#</th>
                <th>Bezeichnung</th>
                <th class="text-center">Verwalter</th>
                <th class="text-center">Betriebe</th>
            </tr>
        </thead>
        <tbody> 
           @foreach ($associations as $ass)
            <tr>
                <td>{!! link_to_route('settings.association/show', $ass->id, array('id' => $ass->id)) !!}</td>
                <td>{!! link_to_route('settings.association/show', $ass->name, array('id' => $ass->id)) !!}</td>
                @if ($ass->wuser_username)
                <td class="text-center">{!! link_to_route('settings.user/show', $ass->wuser_username, array('username' => $ass->wuser_username)) !!}</td>
                @else
                <td class="text-center">{{ ($ass->wuser_username ? : '-') }}</td>
                @endif
                <td class="text-center">{{ $ass->applicants->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop