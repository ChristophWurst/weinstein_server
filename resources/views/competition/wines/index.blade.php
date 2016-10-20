@extends('default')

@section('content')
<h1>Weine</h1>
@if (Session::has('rowsImported'))
<div class="alert alert-success">
    <strong>Import erfolgreich</strong><br>
    {!! Session::get('rowsImported') !!} Datens&auml;tze
    wurden importiert.
</div>
@endif
<div class="form-inline container">
    @if ($show_add_wine)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/create', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-plus"></span>
       Wein hinzuf&uuml;gen
    </a></p>
    @endif
    @if ($competition_admin)
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <span class="glyphicon glyphicon-export"></span> Export<span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li>{!! link_to_route('enrollment.wines/export', 'Alle', array('competition' => $competition->id)) !!}</li>
            <li>{!! link_to_route('enrollment.wines/export-kdb', 'KdB', array('competition' => $competition->id)) !!}</li>
            <li>{!! link_to_route('enrollment.wines/export-sosi', 'SoSi', array('competition' => $competition->id)) !!}</li>
            <li>{!! link_to_route('enrollment.wines/export-chosen', 'Ausschank', array('competition' => $competition->id)) !!}</li>
        </ul>
    </div>
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
    @if ($edit_chosen && $competition_admin)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/import-chosen', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       Auszuschenkende Weine importieren
    </a>
    @endif
    @if ($show_complete_choosing && $competition_admin)
    <a class="btn btn-default"
       type="button"
       href="{!! route('competition/complete-choosing', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-ok"></span>
       Auswahl abschlie&szlig;en
    </a>
    @endif
    @if ($export_flaws)
    <a class="btn btn-default"
       type="button"
       href="{!! route('enrollment.wines/export-flaws', array('competition' => $competition->id)) !!}">
        <span class="glyphicon glyphicon-import"></span>
       Fehlerprotokoll exportieren
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
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-center">Dateinummer</th>
                <th>Betrieb</th>
                <th>Verein</th>
                <th>Marke</th>
                <th>Sorte</th>
                <th>Jahr</th>
                <th class="text-center">Qualit&auml;t</th>
                <th class="text-center">Alk.</th>
                <th class="text-center">Alk. ges.</th>
                <th class="text-center">Zucker</th>
                <th class="text-center">1. Bewertung</th>
                <th class="text-center">2. Bewertung</th>
                <th class="text-center">KdB</th>
                <th class="text-center">Ex</th>
                <th class="text-center">SoSi</th>
                <th class="text-center">Ausschank</th>
                <th></th>
				@if ($show_enrollment_pdf_export)
				<th class="text-center">Formular</th>
				@endif
            </tr>
        </thead>
        <tbody id="wine_list">
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
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
	
	var wines = new Weinstein.Models.WineCollection();
	wines.url = '{{ $wine_url }}';
	var wineList = new Weinstein.Views.WineListView({
		el: $('#wine_list'),
		collection: wines
	});
	wineList.render();
	window.wineList = wineList;
	wines.fetch();
    
});

@stop