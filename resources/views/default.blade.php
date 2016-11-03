<?php
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Weinstein</title>
        <link rel="icon" type="image/png" href="{!! asset('favicon.png') !!}">
        <link rel="apple-touch-icon" href="{!! asset('favicon_high.png') !!}" />

        <!-- Bootstrap -->
        <link href="{!! asset('css/bootstrap.css') !!}" rel="stylesheet">
        <link href="{!! asset('css/bootstrap-theme.css') !!}" rel="stylesheet">
        
        <link href="{!! asset('css/weinstein.css') !!}" rel="stylesheet">
        <link href="{!! asset('js/vendor/select2/dist/css/select2.min.css') !!}" rel="stylesheet">
        <link href="{!! asset('css/select2-bootstrap.css') !!}" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
              <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
            <![endif]-->
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse"
                            data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span> <span
                            class="icon-bar"></span> <span class="icon-bar"></span> <span
                            class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{!! route('start') !!}">
                        <img src="{!! asset('img/ws-logo.png') !!}" alt="Weinstein" />&nbsp;
                        <span class="ws-red">Weinstein</span>
                    </a>
                </div>
                <div class="collapse navbar-collapse">
                    @if (Auth::check())
                    <ul class="nav navbar-nav">
                        @if (!isset($competition))
                        <?php
							$comp = Competition::all()->first();
							$compLink = $comp ? url('competition', array('competition' => $comp->id)) : null;
						?>
                        <li><a href="{!! $compLink !!}">Bewerb</a></li>
                        @else
                        <?php 
							$stateTastingNumbers = in_array($competition->competitionState->description, array('ENROLLMENT', 'TASTINGNUMBERS1', 'TASTINGNUMBERS2')) && $competition->enrollmentFinished();
							$stateTasting = in_array($competition->competitionState->description, array('TASTING1', 'TASTING2'));
						?>
                        <li class="{!! (Request::is('competition/*') && !in_array(Request::Segment(3), array('enrollment', 'tasting', 'evaluation', 'wines')) && Request::Segment(2) !== 'tasting') ? 'active' : '' !!}">
                            <a href="{!! route('competition/show', array('competition' => $competition->id)) !!}">&Uuml;bersicht</a>
                        </li>
                        <li class="{!! Request::segment(3) == 'wines' ? 'active' : '' !!}">
                            <a href="{!! route('enrollment.wines', array('competition' => $competition->id)) !!}">Weine</a>
                        </li>
                        <li class="dropdown {!! Request::segment(1) == 'competition' && (Request::segment(2) == 'tasting' || Request::segment(3) == 'tasting') ? 'active' : '' !!}">
                            <a class="dropdown-toggle" data-toggle="dropdown">Verkostung <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                @if ($competition->administrates(Auth::user()))
                                <li class="{{ Request::segment(4) == 'numbers' ? 'active' : '' }} {{ $stateTastingNumbers ? '' : 'disabled' }}">
                                    <a href="{{ $stateTastingNumbers ? route('tasting.numbers', array('competition' => $competition->id)) : '#' }}">Kostnummern</a>
                                </li>
                                @endif
                                <li class="{{ Request::segment(4) == 'sessions' ? 'active' : '' }} {{ $stateTasting ? '' : 'disabled' }}">
                                    <a href="{{ $stateTasting ? route('tasting.sessions', array('competition' => $competition->id)) : '#' }}">Kostsitzungen</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown {!! Request::segment(3) == 'evaluation' ? 'active' : '' !!}">
                            <a href="{!! route('evaluation', array('competition' => $competition->id)) !!}" class="dropdown-menu-left" data-toggle="dropdown">Auswertung <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li role="presentation" class="dropdown-header">Protokolle</li>
                                @if ($competition->administrates(Auth::user()))
                                <li class="{{ Request::segment(3) == 'evaluation' && Request::segment(4) == 'protocols' ? 'active' : '' }}">
                                    <a href="{{ route('evaluation.protocols', array('competition' => $competition->id)) }}">Kostprotokolle</a>
                                </li>
                                @endif
                                @if ($competition->administrates(Auth::user()) && $competition->competition_state_id === CompetitionState::STATE_FINISHED)
                                <li role="presentation" class="dropdown-header">Kataloge</li>
                                <li class="{{ Request::segment(3) == 'evaluation' && Request::segment(4) == 'catalogues' ? 'active' : '' }}">
                                    <a href="{{ route('evaluation.catalogues/tasting', array('competition' => $competition->id)) }}">Kostkatalog</a>
                                </li>
                                <li class="{{ Request::segment(3) == 'evaluation' && Request::segment(4) == 'catalogues' ? 'active' : '' }}">
                                    <a href="{{ route('evaluation.catalogues/web', array('competition' => $competition->id)) }}">Webkatalog</a>
                                </li>
                                <li class="{{ Request::segment(3) == 'evaluation' && Request::segment(4) == 'catalogues' ? 'active' : '' }}">
                                    <a href="{{ route('evaluation.catalogues/address', array('competition' => $competition->id)) }}">Addresskatalog</a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown {!! Request::segment(1) == 'account' ? 'active' : '' !!}">
                            <a href="{!! route('account') !!}" class="dropdown-menu-left" data-toggle="dropdown">{{ Auth::user()->username }} <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li class="{{ Request::is('account*') ? 'active' : '' }}">
                                    <a href="{{ route('account') }}">Account</a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="{!! route('logout') !!}">abmelden</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown {!! Request::segment(1) == 'settings' ? 'active' : '' !!}">
                            <a href="{!! route('settings') !!}" class="dropdown-menu-left" data-toggle="dropdown">Einstellungen <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li class="{!! Request::segment(2) == '' ? 'active' : '' !!}">
                                    <a href="{!! route('settings') !!}">&Uuml;bersicht</a>
                                </li>
                                <li class="divider">
                                    
                                </li>
                                @if (Auth::user()->isAdmin())
                                <li class="{!! Request::is('settings/competition*') ? 'active' : '' !!}">
                                    <a href="{!! route('settings.competitions'); !!}">Bewerb</a>
                                </li>
                                <li class="{!! Request::segment(2) == 'winesorts' ? 'active' : '' !!}">
                                    <a href="{!! route('settings.winesorts'); !!}">Sorten</a>
                                </li>
                                <li class="{!! Request::segment(2) == 'activitylog' ? 'active' : '' !!}">
                                    <a href="{!! route('settings.activitylog'); !!}">Ereignisse</a>
                                </li>
                                <li class="divider">
                                    
                                </li>
                                @endif
                                <li class="{!! Request::segment(2) == 'users' ? 'active' : '' !!}">
                                    <a href="{!! route('settings.users') !!}">Benutzer</a>
                                </li>
                                <li class="{!! Request::segment(2) == 'associations' ? 'active' : '' !!}">
                                    <a href="{!! route('settings.associations') !!}">Vereine</a>
                                </li>
                                <li class="{!! (Request::segment(2) == 'applicants' || Request::segment(2) == 'applicant') ? 'active' : '' !!}">
                                    <a href="{!! route('settings.applicants') !!}">Betriebe</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    @elseif (!Request::is('login'))
                    {!! Form::open(array('route' => 'postLogin', 'class' => 'navbar-form navbar-right', 'role' => 'form')) !!}
                        <div class="form-group">
                            <input type="text" placeholder="Benutzername" name="username" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="password" placeholder="Passwort" name="password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-default">Anmelden</button>
                    {!! Form::close() !!}
                    @endif
                </div>
                <!--/.nav-collapse -->
            </div><!--/ .container -->
        </div>
        <div>
            @yield('jumbotron')
        </div>
        <div class="container-fluid">
            @yield('content')
        </div><!--/ .container -->

        <script src="{!! asset('js/vendor/jquery/dist/jquery.min.js') !!}"></script>
        <script src="{!! asset('js/vendor/bootstrap/dist/js/bootstrap.min.js') !!}"></script>
		<script src="{!! asset('js/vendor/underscore/underscore.js') !!}"></script>
		<script src="{!! asset('js/vendor/handlebars/handlebars.min.js') !!}"></script>
		<script src="{!! asset('js/vendor/backbone/backbone.js') !!}"></script>
		<script src="{!! asset('js/vendor/backbone.radio/build/backbone.radio.js') !!}"></script>
		<script src="{!! asset('js/vendor/backbone.marionette/lib/backbone.marionette.js') !!}"></script>
        <script src="{!! asset('js/vendor/select2/dist/js/select2.min.js') !!}"></script>
        <script src="{!! asset('js/select2_locale_de.js') !!}"></script>
        <script src="{!! asset('js/weinstein.js') !!}"></script>

        <script>
			$(function(){ 
				$.ajaxSetup({
					headers: {'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'}
				});
			});
            @yield('script')
        </script>
    </body>
</html>
