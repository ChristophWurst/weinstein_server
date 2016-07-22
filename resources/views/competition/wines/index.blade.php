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
</div>
<div class="text-right">
    {!! $wines->render() !!}
</div>
<br>
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
                @if ($show_rating1)
                <th class="text-center">1. Bewertung</th>
                @endif
                @if ($show_rating2)
                <th class="text-center">2. Bewertung</th>
                @endif
                @if ($show_kdb)
                <th class="text-center">KdB</th>
                @endif
                @if ($show_excluded)
                <th class="text-center">Ex</th>
                @endif
                @if ($show_sosi)
                <th class="text-center">SoSi</th>
                @endif
                @if ($show_chosen) 
                <th class="text-center">Ausschank</th>
                @endif
                @if ($show_edit_wine)
                <th></th>
                @endif
            </tr>
        </thead>
        <tbody id="wine_list">
            @foreach ( $wines as $wine )
            <tr>
                <td class="text-center">
                    {!! link_to_route('enrollment.wines/show', ($wine->nr ? : '-'), array('wine' => $wine->id)) !!}
                </td>
                <td>
                    {!! link_to_route('settings.applicant/show',
                                $wine->applicant->label . ' ' . $wine->applicant->lastname,
                                array('applicant' => $wine->applicant->id)) !!}
                </td>
                <td>
                    {!! link_to_route('settings.association/show',
                                $wine->applicant->association->name,
                                array('association' => $wine->applicant->association->id)) !!}
                </td>
                <td>
                    {{ $wine->label ? : '-' }}
                </td>
                <td>
                    {{ $wine->winesort->name }}
                </td>
                <td>
                    {{ $wine->vintage }}
                </td>
                <td class="text-center">
                    @if ($wine->winequality)
                    {{ $wine->winequality->abbr }}
                    @else
                    -
                    @endif
                </td>
                <td class="text-center">
                    {{ str_replace(".", ",", $wine->alcohol) }}
                </td>
                <td class="text-center">
                    @if (!is_null($wine->alcoholtot))
                    {{ str_replace(".", ",", $wine->alcoholtot) }}
                    @else
                    -
                    @endif
                </td>
                <td class="text-center">
                    {{ str_replace(".", ",", $wine->sugar) }}
                </td>
                @if ($show_rating1)
                <td class="text-center">
                    @if ($wine->rating1)
                    {{ str_replace(".", ",", $wine->rating1) }}
                    @else
                    -
                    @endif
                </td>
                @endif
                @if ($show_rating2)
                <td class="text-center">
                    @if ($wine->rating2)
                    {{ str_replace(".", ",", $wine->rating2) }}
                    @else
                    -
                    @endif
                </td>
                @endif
                @if ($show_kdb)
                <td class="text-center wine_kdb">
                    @if (!$edit_kdb && $wine->kdb)
                    <span class="glyphicon glyphicon-ok"></span>
                    @elseif (!$edit_kdb)
                    -
                    @endif
                </td>
                @endif
                @if ($show_excluded)
                <td class="text-center wine_excluded">
                    @if (!$edit_excluded && $wine->excluded)
                    <span class="glyphicon glyphicon-ok"></span>
                    @elseif (!$edit_excluded)
                    -
                    @endif
                </td>
                @endif
                @if ($show_sosi)
                <td class="text-center wine_sosi">
                    @if (!$edit_sosi && $wine->sosi)
                    <span class="glyphicon glyphicon-ok"></span>
                    @elseif (!$edit_sosi)
                    -
                    @endif
                </td>
                @endif
                @if ($show_chosen)
                <td class="text-center wine_chosen">
                    @if ((!$edit_chosen || !$wine->applicant->association->administrates($user)) && $wine->chosen)
                    <span class="glyphicon glyphicon-ok"></span>
                    @elseif (!$edit_chosen || !$wine->applicant->association->administrates($user))
                    -
                    @endif
                </td>
                @endif
                @if ($show_edit_wine && ($competition_admin || is_null($wine->nr)))
                <td>
                    {!! link_to_route('enrollment.wines/edit', 'bearbeiten', array('wine' => $wine->id)) !!}
                    |
                    {!! link_to_route('enrollment.wines/delete', 'l&ouml;schen', array('wine' => $wine->id)) !!}
                </td>
                @else
                <td></td>
                @endif
                <td class="hidden wine_id">{{ $wine->id }}</td>
                <td class="hidden association_admin">{{ $wine->applicant->association->administrates($user) ? 'y' : 'n' }}</td>
                @if ($edit_kdb)
                <td class="hidden update_url">{!! route('enrollment.wines/update-kdb', array('wine' => $wine->id)) !!}</td>
                @elseif ($edit_excluded)
                <td class="hidden update_url">{!! route('enrollment.wines/update-excluded', array('wine' => $wine->id)) !!}</td>
                @elseif ($edit_sosi)
                <td class="hidden update_url">{!! route('enrollment.wines/update-sosi', array('wine' => $wine->id)) !!}</td>
                @elseif ($edit_chosen)
                <td class="hidden update_url">{!! route('enrollment.wines/update-chosen', array('wine' => $wine->id)) !!}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-right">
    {!! $wines->render() !!}
</div>
<!-- /.table-responsive -->
@stop

@section ('script')

@if ($edit_kdb)
$(function() {
    var list = new wineList({
        'list' : '#wine_list',
        'idElem' : '.wine_id',
        'loadUrl' : '{!! route('enrollment.wines/kdb', array('competition' => $competition->id)) !!}',
        'updateUrl' : '.update_url',
        'elem_class' : '.wine_kdb',
        'target' : 'KdB'
    });
});
@elseif ($edit_excluded)
$(function() {
    var list = new wineList({
        'list' : '#wine_list',
        'idElem' : '.wine_id',
        'loadUrl' : '{!! route('enrollment.wines/excluded', array('competition' => $competition->id)) !!}',
        'updateUrl' : '.update_url',
        'elem_class' : '.wine_excluded',
        'target' : 'Ausschluss'
    });
});
@elseif ($edit_sosi)
$(function() {
    var list = new wineList({
        'list' : '#wine_list',
        'idElem' : '.wine_id',
        'loadUrl' : '{!! route('enrollment.wines/sosi', array('competition' => $competition->id)) !!}',
        'updateUrl' : '.update_url',
        'elem_class' : '.wine_sosi',
        'target' : 'SoSi'
    });
});
@elseif ($edit_chosen)
$(function() {
    var list = new wineList({
        'list' : '#wine_list',
        'idElem' : '.wine_id',
        'loadUrl' : '{!! route('enrollment.wines/chosen', array('competition' => $competition->id)) !!}',
        'updateUrl' : '.update_url',
        'elem_class' : '.wine_chosen',
        'target' : 'Auswahl'
    });
});
@endif

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
    
});

@stop