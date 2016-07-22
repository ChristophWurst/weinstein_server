@extends ('competition/tasting/tasting-session/tasting-session')

@section ('main_content')
<h1>Kostsitzungen <small>{{ $tastingstage->id }}. Verkostung</small></h1>
<a class="btn btn-default"
   type="button"
   href="{!! route('tasting.sessions/add', array('competition' => $competition->id)) !!}">
    <span class="glyphicon glyphicon-plus"></span>
   Sitzung erstellen
</a>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Kommissionen</th>
                <th class="text-center">Verkoster</th>
                <th class="text-center">Verkostete Weine</th>
                <th class="text-center">Verwalter</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tastingsessions as $session)
            <tr>
                <td class="col-md-1 text-center">
                    {!! link_to_route('tasting.session/show', $session->nr, array('tastingsession' => $session->id)) !!}
                </td>
                <td class="col-md-1 text-center">
                    {{ $session->commissions()->count() }}
                </td>
                <td class="col-md-1 text-center">
                    {{ $session->GetActiveTastersCount() }}
                </td>
                <td class="text-center">
                    {{ $session->tastedwines()->count() }}
                </td>
                @if ($session->wuser_username)
                <td class="text-center">{!! link_to_route('settings.user/show', $session->wuser_username, array('username' => $session->wuser_username)) !!}</td>
                @else
                <td class="text-center">{{ ($session->wuser_username ? : '-') }}</td>
                @endif
                <td>
                    @if (!$session->locked)
                    {!! link_to_route('tasting.sessions/edit',
                            'bearbeiten',
                            array('tastingsession' => $session->id)) !!}
                    @if ($session->deletable())
                            |
                    {!! link_to_route('tasting.sessions/delete',
                            'l&ouml;schen',
                            array('tastingsession' => $session->id)) !!}
                    @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- /.table-responsive -->
@stop