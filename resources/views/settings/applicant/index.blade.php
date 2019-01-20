@extends('settings/settings')

@section('settings_content')
@if (Session::has('applicant_created'))
<div class="alert alert-success">
    <strong>Betrieb angelegt</strong><br>
    <?php
    list ($user, $password) = Session::get('applicant_created');
    ?>
    Der Betrieb kann sich mit dem Benutzernamen <strong>{{ $user }}</strong> und Passwort <strong>{{ $password }}</strong> anmelden.
</div>
@endif
<h1>Betriebe</h1>
@if (Auth::user()->isAdmin() || $canAdd)
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.applicants/create') !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Betrieb hinzuf&uuml;gen
</a>
@if (Auth::user()->isAdmin())
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.applicants/import') !!}">
    <span class="glyphicon glyphicon-import"></span>
    Daten importieren
</a>
@endif
@else
<div class="alert alert-info" role="alert">
    <strong>Hinweis:</strong> Sie sehen nur Betriebe, die Sie verwalten
</div>
@endif
@if (Session::has('rowsImported'))
<div class="alert alert-success" role="alert">
    <strong>Import erfolgreich</strong><br>
    {!! Session::get('rowsImported') !!} Datens&auml;tze
    wurden importiert.
</div>
@endif
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-center">Betriebsnummer</th>
                <th>Bezeichnung</th>
                <th>Name</th>
                <th>Verein/Stand</th>
                <th class="text-center">Verwalter</th>
                <th>Ort</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applicants as $applicant)
            <tr>
                <td class="text-center">{!! link_to_route('settings.applicant/show', $applicant->id, array('id' => $applicant->id)) !!}</td>
                <td>{{ ($applicant->label ? : '-') }}</td>
                <td>{{ $applicant->firstname }} {{ $applicant->lastname }}</td>
                <td>{!! link_to_route('settings.association/show', $applicant->association->name, array('id' => $applicant->association->id)) !!}</td>
                @if ($applicant->wuser_username)
                <td class="text-center">{!! link_to_route('settings.user/show', $applicant->wuser_username, array('username' => $applicant->wuser_username)) !!}</td>
                @else
                <td class="text-center">{{ ($applicant->wuser_username ? : '-') }}</td>
                @endif
                <td>{{ ($applicant->address->city ? : '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop