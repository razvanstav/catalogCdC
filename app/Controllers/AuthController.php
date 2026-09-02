<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        if (Session::user()) {
            $role = Session::role();
            Response::redirect('/' . $role . '/dashboard');
        }
        View::render('auth/login', [], 'layouts/auth');
    }

    public function login(): void
    {
        $email = Request::input('email');
        $password = Request::input('password');

        if (!$email || !$password) {
            Session::flash('error', 'Vă rugăm să completați emailul și parola.');
            Response::redirect('/login');
        }

        if ($this->authService->login($email, $password)) {
            $role = Session::role();
            Session::flash('success', 'Autentificare reușită! Bine ați revenit.');
            Response::redirect('/' . $role . '/dashboard');
        } else {
            Session::flash('error', 'Credențiale incorecte sau cont inactiv.');
            Response::redirect('/login');
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        Session::flash('success', 'Ați fost deconectat cu succes.');
        Response::redirect('/login');
    }
}
