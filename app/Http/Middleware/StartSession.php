<?php

namespace App\Http\Middleware;

use App\Supports\WebServer;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as Middleware;

class StartSession extends Middleware
{
    /**
     * Get the session implementation from the manager.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSession(Request $request)
    {
        $session = parent::getSession($request);
        if ($token = $request->header('token')) {
            $sessionId = WebServer::decryptCookieValue(
                WebServer::getSessionCookieKey(),
                $token
            );
            $session->setId($sessionId);
        }

        $this->startSession($request, $session);

        return $session;
    }
}
