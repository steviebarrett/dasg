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

            case "view":
                $view = new views\record($this->_model, $_GET["o"] ?? '');
                $view->show();
                break;

            case "edit":
                requireAdmin();
                $view = new views\record($this->_model);
                $view->edit();
                break;

            case "save":
                requireAdmin();

                // CSRF check (controller-level, not view)
                if (
                    empty($_POST['_csrf']) ||
                    empty($_SESSION['csrf_token']) ||
                    !hash_equals($_SESSION['csrf_token'], (string)$_POST['_csrf'])
                ) {
                    http_response_code(403);
                    echo "CSRF validation failed";
                    exit;
                }

                $this->_model->save($_POST);

                if (($_GET["id"] ?? '') === '-1') {
                    $this->_model = new models\record($_POST["ai"] ?? '');
                }

                (new views\record($this->_model))->show();
                break;

            default:
                http_response_code(404);
                echo "Unknown action";
        }
    }
}