@extends('settings/settings')

@section('settings_content')
<h1>Bewerb</h1>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Status</th>
                <th class="text-center">Verwalter</th>
                <th class="text-center">Weine</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($competitions as $c)
            <tr>
                <td>
                    {{  $c->competitionstate->getDescription() }}
                </td>
                @if ($c->wuser_username)
                <td class="text-center">
                    {!! link_to_route('settings.user/show', $c->wuser_username, array('id' => $c->wuser_username)) !!}
                </td>
                @else
                <td class="text-center">
                    -
                </td>
                @endif
                <td class="text-center">
                    {{ $c->wines()->count() }}
                </td>
                <td>
                    <a class="btn btn-danger"
                       type="button"
                       href="{!! route('competition/reset', array('competition' => $c->id)) !!}">
                        <span class="glyphicon glyphicon-repeat"></span>
                       zur&uuml;cksetzen
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop