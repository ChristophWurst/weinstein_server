@extends ('competition/tasting/tasting-session/tasting-session')

@section('main_content')
<h1>{!! $tasting_session->nr !!}. Sitzung - Statistik</h1>
<p>
    <a href="{!! route('tasting.session/show', array('tastingsession' => $tasting_session->id)) !!}"
        accesskey="" type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-chevron-left"></span>
        zur Kostsitzung
    </a>
</p>
<div class="container-fluid">
    @foreach ($tasting_session->commissions as $commission)
    <div class="col-md-12 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h2 class="panel-title">Kommission {{ strtoupper($commission->side) }}</h2>
            </div>
            <div class="panel-body">
                <p>
                    <abbr title="Durchschnitt">X</abbr> = {{ $commission->statistic->avg ? round($commission->statistic->avg, 3) : '-' }}
                </p>
                <p>
                    <abbr title="Standardabweichung">S</abbr> = {{ $commission->statistic->deviation ? round($commission->statistic->deviation, 3) : '-' }}
                </p>
            </div>
            <table class="table table-hover table-responsive">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Verkoster</th>
                        <th class="text-center"><abbr title="Durchschnitt">X</abbr></th>
                        <th class="text-center"><abbr title="Standardabweichung">S</abbr></th>
                        <th class="text-center"><abbr title="Differenz zu Kommissionsdurchschnitt">dX</abbr></th>
                        <th class="text-center"><abbr title="Differenz zu Kommissionsstandardabweichung">dS</abbr></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($commission->tasters as $taster)
                    <tr>
                        <td>{{ $taster->nr }}</td>
                        <td>{{ $taster->name ? : '-' }}</td>
                        <td class="text-center">{{ round($taster->statistic->avg, 3) ? : '-' }}</td>
                        <td class="text-center">{{ round($taster->statistic->deviation, 3) ? : '-' }}</td>
                        <td class="text-center">{{ (!is_null($commission->statistic->avg) && !is_null($taster->statistic->avg)) ? round($commission->statistic->avg - $taster->statistic->avg, 1) : '-' }}</td>
                        <td class="text-center">{{ (!is_null($commission->statistic->deviation) && !is_null($taster->statistic->deviation)) ? round($commission->statistic->deviation - $taster->statistic->deviation, 3) : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@stop