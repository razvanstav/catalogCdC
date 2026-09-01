<?php

namespace App\Middleware;

use App\Support\Session;
use App\Support\Response;

class AuthMiddleware
{
    public function handle(): void
    {
        Session::start();
        if (!Session::has('user')) {
            Session::flash('error', 'Vă rugăm să vă autentificați pentru a continua.');
            Response::redirect('/login');
        }
    }

    public function teacher(): void
    {
        $this->handle();
        if (Session::role() !== 'teacher') {
            Response::forbidden('Accesul la acest modul este rezervat cadrului didactic.');
        }
    }

    public function parent(): void
    {
        $this->handle();
        if (Session::role() !== 'parent') {
            Response::forbidden('Accesul la acest modul este rezervat părinților și tutorilor.');
        }
    }

    public function student(): void
    {
        $this->handle();
        if (Session::role() !== 'student') {
            Response::forbidden('Accesul la acest modul este rezervat elevilor.');
        }
    }
}
