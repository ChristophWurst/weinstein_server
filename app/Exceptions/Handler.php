<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException as BaseValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

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
	 * @param  Exception  $e
	 * @return void
	 */
	public function report(Exception $e) {
		if (app()->bound('sentry') && $this->shouldReport($e)) {
			app('sentry')->setRelease(config('app.version', 'unknown'));
			if (app('auth')->check()) {
				app('sentry')->set_user_data(app('auth')->user()->username);
			} else {
				app('sentry')->set_user_data('anonymous');
			}
			app('sentry')->captureException($e);
		}
		parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  Request  $request
	 * @param  Exception  $e
	 * @return Response
	 */
	public function render($request, Exception $e) {
		return parent::render($request, $e);
	}

}
