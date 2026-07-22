<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // If CSRF token mismatch happens (419), show a friendly message instead of default 419 page.
        if ($e instanceof TokenMismatchException) {
            // For AJAX / JSON requests return JSON with status 419 so frontend can handle it gracefully.
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Usuario activo'], 419);
            }

            // Otherwise redirect back (preserve input) and flash a clear message.
            // This avoids showing the generic 419 error page and informs the user their session
            // was superseded (another login) or expired.
            return redirect()->back()->withInput()->with('warning', 'Usuario activo');
        }

        return parent::render($request, $e);
    }
}
