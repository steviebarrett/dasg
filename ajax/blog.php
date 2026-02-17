<?php

require_once '../includes/include.php';

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = (string)($_REQUEST['action'] ?? ''); // allows GET for read-only, POST for writes

// Enforce method + CSRF for state-changing actions
$stateChanging = in_array($action, ['updateCommentStatus', 'postComment'], true);

if ($stateChanging) {
    if ($method !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
        exit;
    }
    Csrf::validateRequest();
}

// Helper for JSON responses
$sendJson = function(array $payload, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit;
};

switch ($action) {

    case "getMonthEntries":
        // Read-only: GET is fine
        $lang = isset($_GET['lang']) ? htmlentities($_GET["lang"]) : 'en';
        $lang = in_array($lang, ['en', 'gd'], true) ? $lang : 'en';

        $idsRaw = (string)($_GET['ids'] ?? '');
        $ids = $idsRaw === '' ? [] : explode('|', $idsRaw);

        foreach ($ids as $id) {
            $id = (int)htmlentities($id);
            if ($id <= 0) continue;

            $blogEntry = BlogEntries::getBlogEntry($id);
            if (!$blogEntry) continue;

            // Escape anything that can contain user/DB content
            $blogTitle = htmlentities($blogEntry->getTitle());
            $blogFirstLine = htmlentities($blogEntry->getFirstLine($lang));
            $blogDate = htmlentities($blogEntry->getDate($lang));

            echo <<<HTML
                <div class="blogRecentPost">
                    <a href="/blog/{$id}/{$lang}" title="{$blogTitle}">
                        <img width="50" alt="{$blogTitle}" src="/images/blog/{$id}.jpg"/>
                        {$blogTitle}
                        <p class="blogRecentPostDate">{$blogDate}</p>
                        <p>{$blogFirstLine}</p>
                    </a>
                </div>
HTML;
        }
		break;

    case "updateCommentStatus":
        // POST + CSRF already enforced above

        $email = (string)($_SESSION['user'] ?? '');
        if ($email === '') {
            $sendJson(['ok' => false, 'error' => 'Not logged in'], 401);
        }

        $user = Users::getUser($email);
        if (!$user || (int)$user->getIsBlogAdmin() !== 1) {
            $sendJson(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $commentId = (int)($_POST['id'] ?? 0);
        $statusStr = (string)($_POST['status'] ?? '');

        if ($commentId <= 0 || !in_array($statusStr, ['approve', 'reject'], true)) {
            $sendJson(['ok' => false, 'error' => 'Bad request'], 400);
        }

        $status = ($statusStr === 'approve') ? 1 : -1;

        $comment = BlogComments::getBlogComment($commentId);
        if (!$comment) {
            $sendJson(['ok' => false, 'error' => 'Not found'], 404);
        }

        $comment->setApproved($status);
        BlogComments::saveBlogComment($comment);

        $sendJson(['ok' => true]);

        break;

    case "postComment":
        // POST + CSRF already enforced above

        $email = (string)($_SESSION['user'] ?? '');
        if ($email === '') {
            $sendJson(['ok' => false, 'error' => 'Not logged in'], 401);
        }

        $blogId = (int)($_POST['id'] ?? 0);
        $contentRaw = (string)($_POST['userComment'] ?? '');

        if ($blogId <= 0 || trim($contentRaw) === '') {
            $sendJson(['ok' => false, 'error' => 'Bad request'], 400);
        }

        $content = trim(strip_tags($contentRaw));

        $comment = new BlogComment(-1);
        $comment->setBlogId($blogId);
        $comment->setContent($content);
        $comment->setUserEmail($email);
        $comment->setDate(date("Y-m-d H:i:s"));
        $comment->setApproved(0);

        BlogComments::saveBlogComment($comment);

        $sendJson(['ok' => true, 'message' => 'Your comment has been submitted for moderation']);

    default:
        $sendJson(['ok' => false, 'error' => 'Unknown action'], 400);
}


