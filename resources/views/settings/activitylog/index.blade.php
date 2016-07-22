@extends('settings/settings')

@section('settings_content')
<h1>Ereignisse</h1>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Zeitpunkt</th>
                <th>Benutzer</th>
                <th>Ereignis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->user ? $log->user->username : '?' }}</td>
                <td>{!! str_replace(array('[', ']'), array('<b>', '</b>'), $log->message) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop