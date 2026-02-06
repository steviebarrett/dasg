<?php

namespace controllers;

use models\record as RecordModel;
use views\record as RecordView;

class record
{
    private RecordModel $_model;

    public function __construct($ai)
    {
        if (!$ai) {
            $ai = $_GET["ai"] ?? ''; // keep your crawl fallback
        }
        $this->_model = new RecordModel((string)$ai);
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['loggedIn'])) {
            http_response_code(403);
            echo "Not authorised";
            exit;
        }
    }

    private function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo "POST required";
            exit;
        }
    }

    private function requireCsrf(): void
    {
        $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '');
        if (empty($_SESSION['csrf_token']) || $token === '' || !hash_equals((string)$_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo "CSRF failed";
            exit;
        }
    }

    public function run($action): void
    {
        switch ($action) {

            case "view":
                $origin = (string)($_GET["o"] ?? '');
                $view = new RecordView($this->_model, $origin);
                $view->show();
                break;

            case "edit":
                $this->requireAdmin();
                $view = new RecordView($this->_model);
                $view->edit();
                break;

            case "save":
                $this->requireAdmin();
                $this->requirePost();
                $this->requireCsrf();

                $this->_model->save($_POST);

                if (($_GET["id"] ?? null) == -1 && !empty($_POST["ai"])) {
                    $this->_model = new RecordModel((string)$_POST["ai"]);
                }

                $view = new RecordView($this->_model);
                $view->show();
                break;

            default:
                http_response_code(404);
                echo "Unknown action";
        }
    }
}