<?php

namespace App\Support;

class Response
{
    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: " . $url);
        exit;
    }

    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function notFound(string $message = 'Pagina sau resursa solicitată nu a fost găsită.'): void
    {
        http_response_code(404);
        View::render('errors/404', ['message' => $message], 'layouts/main');
        exit;
    }

    public static function forbidden(string $message = 'Nu aveți permisiunea de a accesa această resursă.'): void
    {
        http_response_code(403);
        View::render('errors/403', ['message' => $message], 'layouts/main');
        exit;
    }

    public static function serverError(string $message = 'A apărut o eroare internă de server.'): void
    {
        http_response_code(500);
        View::render('errors/500', ['message' => $message], 'layouts/main');
        exit;
    }
}
