@extends('settings/settings')

@section('settings_content')
@if (Session::has('download_created'))
<div class="alert alert-success">
    <strong>Datei angelegt</strong><br>
    <?php
    list ($name, $path) = Session::get('download_created');
    ?>
    Neuer Download für <i>{{ $name  }}</i> angelegt.
</div>
@endif
<h1>Downloads</h1>
@can('manage-downloads')
<a class="btn btn-default"
   type="button"
   href="{!! route('settings.downloads/create') !!}">
    <span class="glyphicon glyphicon-plus"></span>
    Dabei hochladen
</a>
@endcan
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Datei</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($downloads as $download)
            <tr>
                <td>{!! link_to_route('settings.download/show', $download->name, array('download' => $download->id)) !!}</td>
                <td class="text-right">
                    @can('manage-downloads')
                    {!! link_to_route('settings.download/delete', 'l&ouml;schen', array('download' => $download)) !!}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->

@stop
