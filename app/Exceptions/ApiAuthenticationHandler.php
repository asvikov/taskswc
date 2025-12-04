<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticationHandler extends Exception
{
    public function __invoke(AuthenticationException $exception, Request $request): ?Response
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'Valid bearer token is required for this endpoint',
            ], 401);
        }

        return null;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
