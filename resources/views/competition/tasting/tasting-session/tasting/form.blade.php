@extends('default')

@section('content')
<h1>{{ isset($edit) ? 'Nachverkostung' : 'Verkostung' }}
    <small>{{ $competition->getTastingStage()->id }}. Verkostung</small>
</h1>
@if (count($errors->all()) > 0)
    <div class="alert alert-danger">
        <strong>Fehler!</strong> Bitte korrigieren Sie folgende Eingaben:
        <ul>
            @foreach ($errors->all('<li>:message</li>') as $message)
                {!! $message !!}
            @endforeach
        </ul>
    </div>
@endif
{!! Form::open(array('class' => '', 'role' => 'form')) !!}
    <div class="container-fluid">
        @if (isset($edit))
        <div class="col-md-4 col-lg-3 form-horizontal">
            <div class="pull-right">
                <h2>Kostnummer <strong>{{ $tastingnumber->nr }}</strong></h2>
                <p class="pull-right">
                    Dateinummer {{ $tastingnumber->wine->nr }}
                </p>
            </div>
            <div>
                {!! Form::hidden('tastingnumber_id',
                            $tastingnumber->id) !!}
                @foreach ($commission->tasters()->active()->get() as $taster)
                <div class="form-group">
                    {!! Form::Label($commission->side . $taster->nr, $taster->nr . ' - ' . $taster->name, array('class' => 'col-xs-6 col-sm-9 col-md-8 col-lg-8 control-label')) !!}
                    <div class="col-xs-6 col-sm-3 col-md-4 col-lg-4">
                        {!! Form::text($commission->side . $taster->nr,
                                    '',
                                    array('class' => 'form-control')) !!}
                   </div>
                </div>
                @endforeach
                <div class="container-fluid">
                    {!! Form::textarea('comment', $tastingnumber->wine->comment, array('class' => 'col-md-6 pull-right', 'placeholder' => 'Anmerkungen/Fehler')) !!}
                </div>
                <div class="col-md-offset-4 col-lg-offset-8">
                    {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
                </div>
            </div>
        </div>
        @else
        @foreach ($tastingNumbers as $side => $tastingnumber)
        <div class="col-md-4 col-lg-3 form-horizontal">
            <div class="pull-right">
                <h2>Kostnummer <strong>{{ $tastingnumber->nr }}</strong></h2>
                <p class="pull-right">
                    Dateinummer {{ $tastingnumber->wine->nr }}
                </p>
            </div>
            <div>
                {!! Form::hidden('tastingnumber_id' . ($side === 'a' ? 1 : 2),
                            $tastingnumber->id) !!}
                <?php
					$commission = $tastingSession->commissions()->where('side', $side)->first();
					$tasters = $commission->tasters()->active()->get();
				?>
                @foreach ($tasters as $taster)
                <div class="form-group">
                    {!! Form::Label($side . $taster->nr, $taster->nr . ' - ' . $taster->name, array('class' => 'col-xs-6 col-sm-9 col-md-8 col-lg-8 control-label')) !!}
                    <div class="col-xs-6 col-sm-3 col-md-4 col-lg-4">
                        {!! Form::text($side . $taster->nr,
                                    '',
                                    array('class' => 'form-control')) !!}
                   </div>
                </div>
                @endforeach
                <div class="container-fluid">
                    {!! Form::textarea('comment-' . $side, $tastingnumber->wine->comment, array('class' => 'col-md-6 pull-right', 'placeholder' => 'Anmerkungen/Fehler')) !!}
                </div>
                @if (count($tastingNumbers) == 1 || $side == 'b')
                <div class="col-md-offset-4 col-lg-offset-8">
                    {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>
{!! Form::close() !!}
@stop

@section ('script')

$(function() {
    $('form').find('input[type=text],textarea,select').filter(':visible:first').focus();
});

@stop