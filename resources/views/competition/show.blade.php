<?php
use App\MasterData\CompetitionState;
?>


@extends('default')

@section('content')

<h1>Bewerb</h1>
@if (Auth::user()->isAdmin())
<div class="container-fluid">
    <div class="progress">
        <?php $progress = $competition->competitionState->id / count($competition_states) * 100 ?>
        <div class="progress-bar" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%;">
            <span class="sr-only">{{ $progress }}% erledigt</span>
        </div>
    </div>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th class="col-md-1">#</th>
                <th class="col-md-2">Abschnitt</th>
                <th class="col-md-1 text-center">erledigt</th>
                <th class="col-md-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($competition_states as $state)
            <?php $currentState = $competition->competitionState->id; ?>
            <?php $active = $state->id === $currentState ?>
            <tr>
                <td class="{{ $active ? 'active' : '' }}">{{ $state->id }}</td>
                <td class="{{ $active ? 'active' : '' }}">{{ $state->getDescription() }}</td>
                <td class="{{ $active ? 'active' : '' }} text-center">
                    @if ($currentState > $state->id || $currentState === CompetitionState::STATE_FINISHED)
                    <span class="glyphicon glyphicon-ok"></span>
                    @else
                        @if ($state-> id == $currentState)
                            @if ($currentState == CompetitionState::STATE_ENROLLMENT)
                                {{ $wines_with_nr }}/{{ $wines }} Weinen &uuml;bernommen
                            @elseif ($currentState == CompetitionState::STATE_TASTINGNUMBERS1)
                                {{ $wines_tasting_number1 }}/{{ $wines }} zugewiesen
                            @elseif ($currentState == CompetitionState::STATE_TASTING1)
                                {{ $wines_tasted1 }}/{{ $wines }}
                            @elseif ($currentState == CompetitionState::STATE_KDB)
                                {{ $wines_kdb }} zugewiesen
                            @elseif ($currentState == CompetitionState::STATE_EXCLUDE)
                                {{ $wines_excluded }} ausgeschlossen
                            @elseif ($currentState == CompetitionState::STATE_TASTINGNUMBERS2)
                                {{ $wines_tasting_number2 }} zugewiesen
                            @elseif ($currentState == CompetitionState::STATE_TASTING2)
                                {{ $wines_tasted2 }}/{{ $wines_tasting_number2 }} Weine verkostet
                            @elseif ($currentState == CompetitionState::STATE_SOSI)
                                {{ $wines_sosi }} zugewiesen
                            @elseif ($currentState == CompetitionState::STATE_CHOOSE)
                                {{ $wines_chosen }} ausgew&auml;hlt
                            @endif
                        @endif
                    @endif
                </td>
                <td class="{{ $active ? 'active' : '' }}">
                    @if ($active)
                        @if ($currentState === CompetitionState::STATE_TASTINGNUMBERS1 && $wines == $wines_tasting_number1)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-tastingnumbers', array('competition' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}">
                            <span class="glyphicon glyphicon-ok-sign"></span>
                            Zuweisung abschlie&szlig;en
                        </a>
                        @elseif ($currentState === CompetitionState::STATE_TASTING1 && $wines === $wines_tasted1)
                        <a href="{!! route('competition/complete-tasting', array('tastingsession' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}"
                            type="button" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-ok-sign"></span>
                            1. Verkostung abschlie&szlig;en
                        </a>
                        @elseif ($currentState === CompetitionState::STATE_KDB)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-kdb', array('competition' => $competition->id)) !!}">
                            <span class="glyphicon glyphicon-ok"></span>
                            KdB Zuweisung abschlie&szlig;en
                        </a>
                        @elseif ($currentState === CompetitionState::STATE_EXCLUDE)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-excluded', array('competition' => $competition->id)) !!}">
                            <span class="glyphicon glyphicon-ok"></span>
                            Ausschluss abschlie&szlig;en
                        </a>
                        @elseif ($currentState == CompetitionState::STATE_TASTINGNUMBERS2)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-tastingnumbers', array('competition' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}">
                            <span class="glyphicon glyphicon-ok-sign"></span>
                            Zuweisung abschlie&szlig;en
                        </a>
                        @elseif ($currentState == CompetitionState::STATE_TASTING2 && $wines_tasted2 === $wines_tasting_number2)
                        <a href="{!! route('competition/complete-tasting', array('tastingsession' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}"
                            type="button" class="btn btn-sm btn-default">
                            <span class="glyphicon glyphicon-ok-sign"></span>
                            2. Verkostung abschlie&szlig;en
                        </a>
                        @elseif ($currentState === CompetitionState::STATE_SOSI)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-sosi', array('competition' => $competition->id)) !!}">
                            <span class="glyphicon glyphicon-ok"></span>
                           SoSi Zuweisung abschlie&szlig;en
                        </a>
                        @elseif ($currentState === CompetitionState::STATE_CHOOSE)
                        <a class="btn btn-default"
                           type="button"
                           href="{!! route('competition/complete-choosing', array('competition' => $competition->id)) !!}">
                            <span class="glyphicon glyphicon-ok"></span>
                           Auswahl abschlie&szlig;en
                        </a>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@stop