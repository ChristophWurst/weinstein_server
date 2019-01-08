@extends('competition/tasting/tasting-session/tasting-session')

@section('main_content')
<h1>{!! $data->nr !!}. Sitzung</h1>
<div class="container-fluid">
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">
                    {{ $data->nr }}. Sitzung
                    <span class="pull-right">
                        @if (!$data->locked)
                        <a href="{!! route('tasting.sessions/edit', array('tastingsession' => $data->id)) !!}"
                            accesskey="" type="button" class="btn btn-default btn-xs">
                            <span class="glyphicon glyphicon-edit"></span>
                            bearbeiten
                        </a>
                        @endif
                        <a href="{!! route('tasting.sessions/statistics', array('tastingsession' => $data->id)) !!}"
                            accesskey="" type="button" class="btn btn-default btn-xs">
                            <span class="glyphicon glyphicon-info-sign"></span>
                            Statistik
                        </a>
                    </span>
                </h2>
            </div>
            <div class="panel-body">
                <p>
                    <strong>Verkostungsrunde:</strong>
                    {!! $data->tastingstage_id !!}
                </p>
                <p>
                    <strong>Verwalter:</strong>
                    @if ($data->wuser_username)
                    {!! link_to_route('settings.user/show', $data->wuser_username, array('username' => $data->wuser_username)) !!}
                    @else
                    -
                    @endif
                </p>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Verkoster</h2>
            </div>
            <div id="tasters_error" class="panel-body bg-danger hidden">
                <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
                <ul id="tasters_error_list">
                    @foreach ($errors->all(('<li>:message</li>')) as $message)
                    {!! $message !!}
                    @endforeach
                </ul>
            </div>
            <div class="panel-body container-fluid">
                @foreach ($data->commissions as $commission)
                <div id="tasters-{{ $commission->side }}"
					 class="col-md-6"></div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Nachverkostung</h2><small>nach Kostnummer</small>
            </div>
            <div class="panel-body container-fluid">
                @foreach ($data->commissions as $commission)
                <div class="col-md-6">
                    <h3>Kommission {!! strtoupper($commission->side) !!}</h3>
                    <div class="input-group">
                        {!! Form::text('', '', array('class' => 'form-control', 'id' => 'retaste-tastingnumber-' . $commission->side)) !!}
                        <span class="input-group-btn ">
                            <a id="{!! 'retaste-btn-' . $commission->side !!}" class="btn btn-default" href=""><span class="glyphicon glyphicon-glass"></span></a>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">
                    Verkostete Weine
                    <span class="pull-right">
                        @if (!$tasting_finished && !$data->locked)
                        <a href="{!! route('tasting.session/taste', array('tastingsession' => $data->id)) !!}"
                            id="btn-taste"
                            accesskey="" type="button" class="btn btn-default btn-xs">
                            <span class="glyphicon glyphicon-glass"></span>
                            Verkostung
                        </a>
                        @endif
                        @foreach ($data->commissions as $commission)
                        <a class="btn btn-default btn-xs"
                           type="button"
                           href="{!! route('tasting.sessions/export-result', array('tastingsession' => $data->id, 'commission' => $commission->id)) !!}">
                            <span class="glyphicon glyphicon-export"></span>
                            Ergebnisse ({{ strtoupper($commission->side) }}) exportieren
                        </a>
                        @endforeach
                    </span>
                </h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>KostNr</th>
                        <th>DateiNr</th>
                        <th>Bewertung</th>
                        <th>Nachverkostung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->tastedWines()->orderBy('tastingnumber_nr', 'desc')->get() as $wine)
                    <tr>
                        <td>{{ $wine->tastingnumber_nr }}</td>
                        <td>{{ $wine->wine_nr }}</td>
                        <td>{{ str_replace(".", ",", sprintf("%2.3f", $wine->result)) }}</td>
                        <td>
                            @if ($data->locked)
                            -
                            @else
                            @foreach ($data->commissions as $commission)
                            {!! link_to_route(
                                        'tasting.session/retaste',
                                        strtoupper($commission->side),
                                        array(
                                            'tastingsession' => $data->id,
                                            'tastingnumber' => $wine->tastingnumber_id,
                                            'commission' => $commission->id,
                                        )
                            ) !!}
                            &nbsp;
                            @endforeach
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section ('script')

$(function() {
    $('#btn-taste').focus();
});

@foreach ($data->commissions as $commission)
$(function() {
    var tv = new TastersView({
		el: '#tasters-{{ $commission->side }}',
		side: '{{ $commission->side }}',
		locked: {{ $commission->locked ? 'true' : 'false' }},
		url : '{!! route('tasters.index') !!}',
		commissionId: {{ $commission->id }}
	});
	tv.render();
});
@endforeach

@foreach ($data->commissions as $commission)
$(function() {
    retastebutton({
        'input': '#retaste-tastingnumber-{!! $commission->side !!}',
        'btn': '#retaste-btn-{!! $commission->side !!}',
        'translateUrl': '{!! route('tasting.numbers/translate', array('competition' => $competition->id, 'id' => ':id')) !!}',
        'btnUrl': '{!! route('tasting.session/retaste', array('tastingsession' => $data->id, 'tastingnumber' => ':tnr', 'commission' => $commission->id)) !!}'
    });
});
@endforeach

@stop