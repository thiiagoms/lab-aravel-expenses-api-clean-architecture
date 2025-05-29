<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(fn (Throwable $e): JsonResponse => match (true) {
            $e instanceof AuthenticationException, $e instanceof AccessDeniedHttpException => response()->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED),
            $e instanceof DomainException, $e instanceof InvalidSignatureException => response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST),
            default => response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR)
        });
    })->create();

$app->setBasePath(dirname(__DIR__));
$app->useAppPath(dirname(__DIR__).'/src');

return $app;
