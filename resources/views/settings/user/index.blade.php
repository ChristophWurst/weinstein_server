@extends('settings/settings')

@section('settings_content')
<h1>Benutzer</h1>
@if (Auth::user()->admin)
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.users/create') !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Benutzer hinzuf&uuml;gen
</a>
@endif
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Benutzername</th>
                <th>Administrator</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
            <tr>
                <td>
                    {!! link_to_route('settings.user/show', $u->username, array('username' => $u->username)) !!}
                </td>
                <td>
                    @if ($u->admin)
                    <span class="glyphicon glyphicon-ok"></span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    {!! link_to_route('settings.users/edit', 'bearbeiten', 
                                array('username' => $u->username)) !!}
                    @if($u->username != Auth::user()->username)
                        | {!! link_to_route('settings.users/delete', 'l&ouml;schen', array('username' => $u->username)) !!}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop