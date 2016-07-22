@extends ('default')

@section ('content')
<h1>Kostnummern <small>{{ $competition->getTastingStage()->id }}. Verkostung</small></h1>
@if (Session::has('rowsImported'))
<div class="alert alert-success">
    <strong>Import erfolgreich</strong><br>
    {!! Session::get('rowsImported') !!} Datens&auml;tze
    wurden importiert.
</div>
@endif
@if ($show_add)
<a class="btn btn-default"
@else
<a class="btn btn-default disabled"
@endif
   type="button"
   href="{!! route('tasting.numbers/assign', array('competition' => $competition->id)) !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Kostnummer zuweisen
</a>
@if ($show_add)
<a class="btn btn-default"
@else
<a class="btn btn-default disabled"
@endif
   type="button"
   href="{!! route('tasting.numbers/import', array('competition' => $competition->id)) !!}">
    <span class="glyphicon glyphicon-import"></span>
   Zuweisung importieren
</a>
@if ($finished)
<a class="btn btn-default"
   type="button"
   href="{!! route('competition/complete-tastingnumbers', array('competition' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}">
    <span class="glyphicon glyphicon-ok-sign"></span>
    Zuweisung abschlie&szlig;en
</a>
@endif
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-center">KostNr</th>
                <th class="text-center">DateiNr</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($numbers as $num)
            <tr>
                <td class="col-md-1 text-center">{{ $num->nr }}</td>
                <td class="text-center">{{ $num->wine->nr }}</td>
                <td>{!! link_to_route('tasting.numbers/deallocate',
                            'aufheben',
                            array('tastingnumber' => $num->id)) !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop