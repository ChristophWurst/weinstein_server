@extends ('competition/tasting/tasting-session/tasting-session')

<?php $edit = isset($data); ?>

@section('main_content')
<h1>Kostsitzung {!! $edit ? '&auml;ndern' : 'erstellen' !!}</h1>
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
{!! Form::open(array('class' => 'form-horizontal', 'role' => 'form')) !!}
    @if (!$edit)
    <div class="form-group">
        {!! Form::Label('commissions', 'Kommissionen', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-4 col-md-1">
            {!! Form::select('commissions', array(1 => 1, 2 => 2), 2, array('class' => 'form-control col-md-3')) !!}
        </div>
    </div>
    @endif
    <div class="form-group">
        {!! Form::Label('wuser_username', 'Verwalter', array('class' => 'col-sm-2 col-md-2 control-label')) !!}
        <div class="col-sm-10 col-md-3">
            @if (Auth::user()->admin)
            {!! Form::select('wuser_username', $users, ($edit ? $data['wuser_username'] : 'none'), array('class' => 'form-control col-md-3')) !!}
            @else
            {!! Form::select('wuser_username', $users, ($edit ? $data['wuser_username'] : 'none'), array('class' => 'form-control col-md-3', 'readonly' => true)) !!}
            @endif
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10 col-md-offset-2">
            {!! Form::submit('Speichern', array('class' => 'btn btn-default')) !!}
        </div>
    </div>
{!! Form::close() !!}
@stop