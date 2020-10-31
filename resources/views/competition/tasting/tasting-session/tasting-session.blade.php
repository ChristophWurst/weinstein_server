@extends ('default')

@section ('content')
<div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            <li class="{!! is_null(Route::input('tastingsession')) ? 'active' : '' !!}">
                <a href="{!! route('tasting.sessions', array('competition' => $competition->id)) !!}">&Uuml;bersicht</a>
            </li>
            @foreach ($tastingsessions as $ts)
            <li class="{{ (!is_null(Route::input('tastingsession')) && Route::input('tastingsession')->id == $ts->id) ? 'active' : '' }}">
                {!! link_to_route('tasting.session/show',
                            "Sitzung " . $ts->nr,
                            array('tastingsession' => $ts->id)) !!}
            </li>
            @endforeach
            <li class="divider"></li>
            <li>
                <div class="text-center">
                    @if ($show_finish1)
                    <a href="{!! route('competition/complete-tasting', array('competition' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}"
                        type="button" class="btn btn-sm btn-default">
                        <span class="glyphicon glyphicon-ok-sign"></span>
                        1. Verkostung abschlie&szlig;en
                    </a>
                    @elseif ($show_finish2)
                    <a href="{!! route('competition/complete-tasting', array('competition' => $competition->id, 'tasting' => $competition->getTastingStage()->id)) !!}"
                        type="button" class="btn btn-sm btn-default">
                        <span class="glyphicon glyphicon-ok-sign"></span>
                        2. Verkostung abschlie&szlig;en
                    </a>
                    @endif
                </div>
            </li>
        </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        @yield('main_content')
    </div>
</div>
@stop