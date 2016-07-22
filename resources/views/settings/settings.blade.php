@extends('default')

@section('content')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="{!! Request::segment(2) == '' ? 'active' : '' !!}">
                <a href="{!! route('settings') !!}">&Uuml;bersicht</a>
            </li>
        </ul>
        @if (Auth::user()->admin)
        <ul class="nav nav-sidebar">
            <li class="{!! Request::is('settings/competition*') ? 'active' : '' !!}">
                <a href="{!! route('settings.competitions') !!}">Bewerb</a>
            </li>
            <li class="{!! Request::is('settings/winesort*') ? 'active' : '' !!}">
                <a href="{!! route('settings.winesorts') !!}">Sorten <span class="badge pull-right">{!! WineSort::count() !!}</span></a>
            </li>
            <li class="{!! Request::segment(2) == 'activitylog' ? 'active' : '' !!}">
                <a href="{!! route('settings.activitylog') !!}">Ereignisse</a>
            </li>
        </ul>
        @endif
        <ul class="nav nav-sidebar">
            <li class="{!! Request::segment(2) == 'users' ? 'active' : '' !!}">
                <a href="{!! route('settings.users') !!}">Benutzer <span class="badge pull-right">{!! User::count() !!}</span></a>
            </li>
            <li class="{!! Request::segment(2) == 'associations' ? 'active' : '' !!}">
                <a href="{!! route('settings.associations') !!}">Vereine <span class="badge pull-right">{!! Association::count() !!}</span></a>
            </li>
            <li class="{!! (Request::segment(2) == 'applicants' || Request::segment(2) == 'applicant') ? 'active' : '' !!}">
                <a href="{!! route('settings.applicants') !!}">Betriebe <span class="badge pull-right">{!! Applicant::count() !!}</span></a>
            </li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        @yield('settings_content')
    </div>
</div>
@stop