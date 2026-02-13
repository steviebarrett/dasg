<?php
declare(strict_types=1);

require_once '../includes/include.php';

header('Content-Type: application/json; charset=UTF-8');

$action = (string)($_GET['action'] ?? '');
$lang   = (string)($_GET['lang'] ?? 'en');
$lang   = in_array($lang, ['en', 'gd'], true) ? $lang : 'en';

switch ($action) {

    case 'checkregistered':
        $email = trim((string)($_GET['email'] ?? ''));

        $msg = [
            'en' => 'This email address is already registered',
            'gd' => 'Tha an seòladh puist-d. seo clàraichte mar thà',
        ];

        // Optional: validate email format first to reduce DB calls
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(true);
            exit;
        }

        echo json_encode(Users::getUser($email) ? $msg[$lang] : true);
        exit;

    case 'checkusername':
        $username = trim((string)($_GET['username'] ?? ''));

        $msg = [
            'en' => 'That username is already taken',
            'gd' => 'Chaidh an t-ainm neach-cleachdaidh seo a chlàradh mar thà',
        ];

        if ($username === '') {
            echo json_encode(true);
            exit;
        }

        echo json_encode(Users::checkUsernameExists($username) ? $msg[$lang] : true);
        exit;

    default:
        echo json_encode(true);
        exit;
}

