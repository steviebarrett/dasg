<?php
declare(strict_types=1);

namespace controllers;

use views\admin as AdminView;
use models\records as RecordsModel;

final class admin extends ControllerBase
{
    public function run($action = null): void
    {
        $model = new RecordsModel();
        $view  = new AdminView($model);

        switch ((string)$action) {

            case 'login':
                // state-changing
                $this->requirePost();
                $this->requireCsrf();

                $username = (string)($_POST['u'] ?? '');
                $password = (string)($_POST['p'] ?? '');

                // constant-time comparisons
                if (!hash_equals((string)ADMIN_USERNAME, $username) || !hash_equals((string)ADMIN_PASSWORD, $password)) {
                    $_SESSION['loggedIn'] = false;
                    // rotate anyway to reduce fixation/spraying value
                    if (!headers_sent()) {
                        session_regenerate_id(true);
                    }

                    $view->writeLoginForm();
                    return;
                }

                $_SESSION['loggedIn'] = true;
                if (!headers_sent()) {
                    session_regenerate_id(true);
                }
                $view->show();
                return;

            case 'logout':
                // state-changing
                $this->requirePost();
                $this->requireCsrf();
                $this->requireAdmin();

                $_SESSION['loggedIn'] = false;
                if (!headers_sent()) {
                    session_regenerate_id(true);
                }
                $view->writeLoginForm();
                return;

            default:
                // read-only but sensitive
                $this->requireAdmin();
                $view->show();
                return;
        }
    }
}