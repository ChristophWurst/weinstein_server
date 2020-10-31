<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException as BaseValidationException;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use function Sentry\configureScope;

class Handler extends ExceptionHandler
{

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		HttpException::class,
		ModelNotFoundException::class,
		BaseValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  Throwable $e
	 * @return void
	 */
	public function report(Throwable $e)
	{
		if (app()->bound('sentry') && $this->shouldReport($e)) {
            /** @var Hub $sentry */
            $sentry = app('sentry');

            $sentry->withScope(function(Scope $scope) use ($e, $sentry) {
                $scope->setTag('release', config('app.version', 'unknown'));
                if (app('auth')->check()) {
                    $scope->setUser([
                        'username' => app('auth')->user()->username,
                    ]);
                }

                $sentry->captureException($e);
            });
		}
		parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  Request $request
	 * @param  Throwable $e
	 * @return Response
	 */
	public function render($request, Throwable $e)
	{
		return parent::render($request, $e);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Illuminate\Auth\AuthenticationException $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		if ($request->expectsJson()) {
			return response()->json(['error' => 'Unauthenticated.'], 401);
		}

		return redirect()->guest('login');
	}

}
