<?php

namespace controllers;

use models;
use views;

final class records
{
    public function run(string $action): void
    {
        $model = new models\records();
        $view  = new views\records($model);

        switch ($action) {

            // Public browse/list (no CSRF/admin needed)
            case 'list': {
                $view->show();
                return;
            }

            // Public search page (no CSRF/admin needed)
            // - showSearchForm if no query terms
            // - show results view if query present
            case 'search': {

                // Advanced search uses s[...] and searchField[...] etc.
                // If no search terms, show the form.
                if (empty($_GET['s']) || (is_array($_GET['s']) && count(array_filter($_GET['s'], 'strlen')) === 0)) {
                    $view->showSearchForm();
                    return;
                }

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