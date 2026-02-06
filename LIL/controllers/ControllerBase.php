<?php
declare(strict_types=1);

namespace controllers;

abstract class ControllerBase
{
    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo 'POST required';
            exit;
        }
    }

    protected function requireAdmin(): void
    {
        if (empty($_SESSION['loggedIn'])) {
            http_response_code(403);
            echo 'Admin required';
            exit;
        }
    }

    protected function requireCsrf(): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (!is_string($sessionToken) || $sessionToken === '') {
            http_response_code(403);
            echo 'CSRF missing';
            exit;
        }

        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $post   = $_POST['_csrf'] ?? '';
        $token  = is_string($header) && $header !== '' ? $header : (is_string($post) ? $post : '');

        if ($token === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo 'CSRF failed';
            exit;
        }
    }
}