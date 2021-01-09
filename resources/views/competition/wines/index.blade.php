@extends('default')

@section('head')
<script src="{!! asset('js/weinstein-wines.js') !!}"></script>
@endsection

@section('content')
<h1>Weine</h1>
@if (Session::has('rowsImported'))
<div class="alert alert-success">
    <strong>Import erfolgreich</strong><br>
    {!! Session::get('rowsImported') !!} Datens&auml;tze
    wurden importiert.
</div>
@endif
<div class="form-inline">
    @if ($show_add_wine)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/create', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-plus"></span>
       Wein hinzuf&uuml;gen
    </a>
    @endif
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <span class="glyphicon glyphicon-export"></span> Export<span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li>{!! link_to_route('enrollment.wines/export', 'Alle', array('competition' => $competition->id)) !!}</li>
            @if ($competition_admin)
            <li>{!! link_to_route('enrollment.wines/export-kdb', 'KdB', array('competition' => $competition->id)) !!}</li>
            <li>{!! link_to_route('enrollment.wines/export-sosi', 'SoSi', array('competition' => $competition->id)) !!}</li>
            <li>{!! link_to_route('enrollment.wines/export-chosen', 'Ausschank', array('competition' => $competition->id)) !!}</li>
            @endif
        </ul>
    </div>
    @if ($export_flaws)
        @can('export-wines-flaws')
            <a class="btn btn-default"
               type="button"
               href="{!! route('enrollment.wines/export-flaws', array('competition' => $competition->id)) !!}">
                <span class="glyphicon glyphicon-import"></span>
                Fehlerprotokoll exportieren
            </a>
        @endcan
    @endif
    @if ($edit_kdb)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/import-kdb', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       KdB importieren
    </a>
    @endif
    @if ($show_complete_kdb)
    <a class="btn btn-default"
       type="button"
       href="{!! route('competition/complete-kdb', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-ok"></span>
       KdB Zuweisung abschlie&szlig;en
    </a>
    @endif
    @if ($edit_excluded)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/import-excluded', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       Ausschluss importieren
    </a>
    @endif
    @if ($show_complete_exclude)
    <a class="btn btn-default"
       type="button"
       href="{!! route('competition/complete-excluded', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-ok"></span>
       Ausschluss abschlie&szlig;en
    </a>
    @endif
    @if ($edit_sosi)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/import-sosi', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       SoSi importieren
    </a>
    @endif
    @if ($show_complete_sosi)
    <a class="btn btn-default"
       type="button"
       href="{!! route('competition/complete-sosi', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-ok"></span>
       SoSi Zuweisung abschlie&szlig;en
    </a>
    @endif
    @if ($edit_chosen)
        @can('sign-chosen', $competition)
        <a class="btn btn-primary"
           type="button"
           href="{!! route('competition/sign-chosen', array('competition' => $competition->id)) !!}">
            <span class="glyphicon glyphicon-ok"></span>
            Auswahl abschlie&szlig;en
        </a>
        @endcan
    @endif
	@if ($show_import_catalogue_numbers && $competition_admin)
    <a class="btn btn-default"
       type="button"
       href="{!! route('cataloguenumbers.import', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       Katalognummern importieren
    </a>
    @endif
	@if ($show_complete_catalogue_numbers && $competition_admin)
    <a class="btn btn-default"
       type="button"
       href="{!! route('competition/complete-catalogue-numbers', array('competition' => $competition)) !!}">
        <span class="glyphicon glyphicon-ok"></span>
       Katalognummernzuweisung abschlie&szlig;en
    </a>
    @endif
	@if ($competition_admin)
    <div class="container-fluid pull-left">
        <div class="input-group" id="search-wine">
            <input type="text"
                   value=""
                   class="form-control"
                   placeholder="Dateinummer">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
            </span>
        </div><!-- /input-group -->
    </div>
	@endif
</div>
<div id="wines-table" class="table-responsive">
</div>
<!-- /.table-responsive -->

<script type="text/javascript">
	@if (!is_null($competition->wuser_username))
    setCompetitionAdmin('{{ $competition->wuser_username }}')
	@endif
</script>

@stop

@section ('script')

$(function() {
    var url = '{!! route('enrollment.wines', array('competition' => $competition->id)) !!}/redirect/';

    var input = $('#search-wine input');
    input.val(null); // empty input
    var button = $('#search-wine button');

    function getUrl(id) {
        return url + id;
    }

    function submit() {
        var id = input.val();
        window.location.href = getUrl(id);
    }

    button.click(function(e) {
        submit();
    });

    renderWineTable(
        '{{ $wine_url }}',
        {{ $competition->id }}
    );
});

@stop
