<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiNotFoundHttpHandler extends Exception
{
    public function __invoke(NotFoundHttpException $exception, Request $request): ?Response
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => 'The requested endpoint does not exist',
                'path' => $request->path(),
                'method' => $request->method(),
            ], 404);
        }

        return null;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
