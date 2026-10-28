<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * API pure : pas de redirection vers une route "login" web.
     * Retourner null fait lever une AuthenticationException rendue en 401 JSON.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
