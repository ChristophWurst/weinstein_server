@extends('settings/settings')

@section('settings_content')
<h1>Einstellungen</h1>
<ul>
    <li>
        {!! link_to_route('settings.users', 'Benutzer') !!}
    </li>
    @if (Auth::user()->isAdmin())
    <li>
        {!! link_to_route('settings.competitions', 'Bewerbe') !!}
    </li>
    @endif
    <li>
        {!! link_to_route('settings.associations', 'Vereine') !!}
    </li>
    <li>
        {!! link_to_route('settings.applicants', 'Betriebe') !!}
    </li>
    @can ('manage-downloads')
    <li>
        {!! link_to_route('settings.downloads', 'Downloads') !!}
    </li>
    @endif
</ul>
@stop
