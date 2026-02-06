<?php

namespace controllers;

use models;
use views;

final class record
{
    private models\record $_model;

    public function __construct(?string $ai = null)
    {
        // Backup for crawlers: allow ai via GET if not passed.
        $ai = $ai ?? (string)($_GET['ai'] ?? '');

        $this->_model = new models\record($ai);
    }

    public function run(string $action): void
    {
        switch ($action) {

            // Public (no CSRF/admin needed)
            case 'view': {
                $origin = (string)($_GET['o'] ?? '');
                $view = new views\record($this->_model, $origin);
                $view->show();
                return;
            }

            // Admin-only (no CSRF required because it renders a form, does not mutate state)
            case 'edit': {
                if (empty($_SESSION['loggedIn'])) {
                    http_response_code(403);
                    echo 'Not authorised';
                    return;
                }

                $view = new views\record($this->_model);
                $view->edit();
                return;
            }

            // Admin-only + CSRF (mutates state)
            case 'save': {
                if (empty($_SESSION['loggedIn'])) {
                    http_response_code(403);
                    echo 'Not authorised';
                    return;
                }

                // CSRF: accept either header (AJAX) or POST param (plain form)
                $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));
                if (empty($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], $token)) {
                    http_response_code(403);
                    echo 'CSRF failed';
                    return;
                }

                // Only accept POST for saves
                if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                    http_response_code(405);
                    echo 'POST required';
                    return;
                }

                $this->_model->save($_POST);

                // If a new record was created, reload it
                $id = (int)($_GET['id'] ?? 0);
                if ($id === -1 && isset($_POST['ai'])) {
                    $this->_model = new models\record((string)$_POST['ai']);
                }

                $view = new views\record($this->_model);
                $view->show();
                return;
            }

            default:
                http_response_code(404);
                echo 'Unknown action';
                return;
        }
    }
}