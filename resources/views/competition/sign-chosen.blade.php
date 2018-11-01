@extends('default')

@section('content')
<h1>Auswahl abschlie&szlig;en</h1>
<div class="container-fluid">
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">W&auml;hlen Sie den Verein, f&uuml;r den Sie die Auswahl der auszuschenkenden Weine abschlie&szlig;en wollen</h2>
            </div>
            <div class="panel-body">
                <table class="table table-hover table-responsive">
                    <thead>
                    <tr>
                        <th>Verein</th>
                        <th class="text-center">Weine insgesamt</th>
                        <th class="text-center">ausgew&auml;hlt</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($associations as $entry)
                        <tr>
                            <td>{{ $entry['association']->name }}</td>
                            <td class="text-center">{{ $entry['total'] }}</td>
                            <td class="text-center">{{ $entry['chosen'] }}</td>
                            <td>
                                @unless ($entry['signed-off'])
                                <form method="post"
                                      action="{{ route('competition/sign-chosen-submit', array('competition' => $competition->id, 'association' => $entry['association'])) }}">
                                    {{ csrf_field() }}
                                    <button class="btn btn-primary"
                                       type="submit">Auswahl abschlie&szlig;en</button>
                                </form>
                                @else
                                Bereits abgeschlossen
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@stop