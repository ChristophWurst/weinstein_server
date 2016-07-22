@extends('settings/settings')

@section('settings_content')
<h1>Sorten</h1>
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.winesorts/create') !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Sorte hinzuf&uuml;gen
</a>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-center">Sortennummer</th>
                <th>Bezeichnung</th>
                <th class="text-center">Weine</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sorts as $sort)
            <tr>
                <td class="col-md-1 text-center">{{ $sort->order }}</td>
                <td>{{ $sort->name }}</td>
                <td class="text-center">{{ $sort->wines()->count() }}</td>
                <td>{!! link_to_route('settings.winesorts/edit', 'bearbeiten', array('winesort' => $sort->id)) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop