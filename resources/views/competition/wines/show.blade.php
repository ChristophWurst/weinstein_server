@extends('default')

@section('content')

<h1>Wein {!! $wine->nr !!}</h1>
<a href="{!! route('enrollment.wines', array('competition' => $wine->competition->id)) !!}"
   type="button"
   class="btn btn-default">
    <span class="glyphicon glyphicon-chevron-left"></span> zur&uuml;ck
</a>
@if ($show_edit_wine)
<a href="{!! route('enrollment.wines/edit', array('wine' => $wine->id)) !!}"
   type="button"
   class="btn btn-default">
    <span class="glyphicon glyphicon-edit"></span> bearbeiten
</a>
<a href="{!! route('enrollment.wines/delete', array('wine' => $wine->id)) !!}"
   type="button"
   class="btn btn-default">
    <span class="glyphicon glyphicon-remove">
    </span> l&ouml;schen
</a>
@endif
<p></p>

<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Stammdaten</h3>
            </div>
            <div class="panel-body row">
                <div class="col-md-6">
                    Betrieb
                </div>
                <div class="col-md-6">
                    {!! link_to_route('settings.applicant/show',
                    $wine->applicant->label . ' ' . $wine->applicant->lastname,
                    array('applicant' => $wine->applicant->id)) !!}
                </div>

                <div class="col-md-6">
                    Verein
                </div>
                <div class="col-md-6">
                    {!! link_to_route('settings.association/show',
                    $wine->applicant->association->name,
                    array('association' => $wine->applicant->association->id)) !!}
                </div>

                <div class="col-md-6">
                    Sorte
                </div>
                <div class="col-md-6">
                    {{ $wine->winesort->name }}
                </div>

                <div class="col-md-6">
                    Marke
                </div>
                <div class="col-md-6">
                    {{ $wine->label ? : '-' }}
                </div>

                <div class="col-md-6">
                    Jahr
                </div>
                <div class="col-md-6">
                    {{ $wine->vintage }}
                </div>

                <div class="col-md-6">
                    Qualit&auml;sstufe
                </div>
                <div class="col-md-6">
                    @if ($wine->winequality)
                    {{ $wine->winequality->label }}
                    @else
                    -
                    @endif
                </div>

                <div class="col-md-6">
                    Säure
                </div>
                <div class="col-md-6">
                    {{ str_replace(".", ",", $wine->acidity) }}
                </div>

                <div class="col-md-6">
                    Vorhandener Alkohol
                </div>
                <div class="col-md-6">
                    {{ str_replace(".", ",", $wine->alcohol) }}
                </div>

                <div class="col-md-6">
                    Zucker
                </div>
                <div class="col-md-6">
                    {{ str_replace(".", ",", $wine->sugar) }}
                </div>
            </div>
        </div>
    </div>
</div>


@stop
