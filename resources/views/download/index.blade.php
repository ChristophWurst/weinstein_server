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
                <td><a href="{{ asset('storage/' . $download->path) }}">{{ $download->name }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->

@stop
