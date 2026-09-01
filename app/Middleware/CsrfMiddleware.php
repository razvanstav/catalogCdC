<?php

namespace App\Middleware;

use App\Support\Csrf;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

class CsrfMiddleware
{
    public function handle(): void
    {
        if (Request::isPost()) {
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Csrf::validate($token)) {
                Session::flash('error', 'Sesiunea de securitate a expirat (Eroare CSRF). Vă rugăm să reîncărcați pagina și să reîncercați.');
                Response::forbidden('Token CSRF invalid sau expirat.');
            }
        }
    }
}
