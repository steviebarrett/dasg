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
        // Only enforce CSRF for state-changing requests
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'POST') {
            return;
        }

        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
        if ($sessionToken === '') {
            $this->csrfFail();
        }

        // Accept either header or form field
        $header = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $post   = (string)($_POST['_csrf'] ?? '');

        $token = $header !== '' ? $header : $post;
        if ($token === '' || !hash_equals($sessionToken, $token)) {
            $this->csrfFail();
        }
    }

    protected function csrfFail(): void
    {
        // If headers already sent, avoid http_response_code warning.
        if (!headers_sent()) {
            http_response_code(403);
        }
        echo 'CSRF failed';
        exit;
    }
}