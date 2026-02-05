<?php

declare(strict_types=1);

namespace controllers;

use views, models;

class admin
{
    public function run($action = null) {
        $model = new models\records();
        $view  = new views\admin($model);

        switch ($action) {

            case "logout":
                $_SESSION["loggedIn"] = false;
                session_regenerate_id(true);
                $view->writeLoginForm();
                break;

            case "login":
                $username = isset($_POST["u"]) ? (string)$_POST["u"] : '';
                $password = isset($_POST["p"]) ? (string)$_POST["p"] : '';

                if (!hash_equals(ADMIN_USERNAME, $username) || !hash_equals(ADMIN_PASSWORD, $password)) {
                    $_SESSION["loggedIn"] = false;
                    $view->writeLoginForm();
                    break;
                }

                $_SESSION["loggedIn"] = true;
                session_regenerate_id(true); // prevent session fixation
                $view->show();
                break;

            default:
                if (!empty($_SESSION["loggedIn"])) {
                    $view->show();
                } else {
                    $view->writeLoginForm();
                }
                break;
        }
    }
}