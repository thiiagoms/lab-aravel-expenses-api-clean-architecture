<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Src\Application\UseCases\Expense\Exceptions\ExpenseNotFoundException;
use Src\Application\UseCases\User\Exceptions\UserNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(fn (Throwable $e): JsonResponse => match (true) {
            $e instanceof AuthenticationException, $e instanceof AccessDeniedHttpException => response()->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED),
            $e instanceof DomainException, $e instanceof InvalidSignatureException => response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => response()->json(['error' => 'resource not found'], Response::HTTP_NOT_FOUND),
            default => response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR)
        });
    })->create();

$app->setBasePath(dirname(__DIR__));
$app->useAppPath(dirname(__DIR__).'/src');

return $app;
