<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
        // Todo /api/* responde errores en JSON (401, 403, 404, 422…), aunque la
        // petición no mande Accept: application/json. Sin esto un 401 de la
        // API terminaba en una redirección al login web.
        $this->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
