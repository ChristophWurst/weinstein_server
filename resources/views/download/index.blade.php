@extends('default')

@section('content')
<h1>Downloads</h1>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Datei</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($downloads as $download)
            <tr>
                <td>{!! link_to_route('settings.download/show', $download->name, array('id' => $download->id)) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->

@stop
