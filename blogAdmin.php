<?php

//ini_set("display_errors", 1);

session_start();

$javascriptBlock = <<<HTML
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.22.1/ckeditor.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#blogPost').validate();

    if ($.fn.datetimepicker) {
        $('#publish_date').datetimepicker({
            format: 'Y-m-d H:i:s',
            step: 15
        });
    }

    if (window.CKEDITOR) {
        CKEDITOR.replace('content_en', {
            versionCheck: false
        });

        CKEDITOR.replace('content_gd', {
            versionCheck: false
        });
    }
});
</script>
HTML;

$cqpPage = true;

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$pageSlug = "blogAdmin";
$pageTitle = "DASG Blog Admin";

//DEV directory!!
$blogImageDir = ROOT . "/images/blog/";

require_once 'includes/htmlHeader.php';

Functions::showLoginForm();

if (isset($_SESSION["user"])) {
    $user = Users::getUser($_SESSION["user"]);

    //check for blog admin status
    if ($user->getIsBlogAdmin() != 1) {
        Functions::writeError("You are not authorised to view this page");
    }

    echo '<a href="commentAdmin.php" title="Comment Admin">Comment Admin</a><br/><br/>';

    //save posted blog entry
    if (isset($_POST["id"])) {
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$postId) {
            Functions::writeError("Invalid blog entry ID.");
        }

        //process the image
        if (!empty($_FILES["image"]["name"])) {
            $tmpName = $_FILES['image']['tmp_name'] ?? '';

            if (!is_uploaded_file($tmpName)) {
                Functions::writeError("Image upload failed.");
            }

            $ext = strtolower(pathinfo((string)($_FILES['image']['name'] ?? ''), PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedExt, true)) {
                Functions::writeError("Unsupported image type.");
            }

            $baseDir = realpath($blogImageDir);
            if ($baseDir === false) {
                Functions::writeError("Image upload directory not found.");
            }

            $safeName = 'upload_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $filepath = $baseDir . DIRECTORY_SEPARATOR . $safeName;

            if (move_uploaded_file($tmpName, $filepath) === false) {
                Functions::writeError("Image upload failed.");
            }

            try {
                $image = new Imagick($filepath);
                $image->setImageFormat("jpeg");
                $image->thumbnailImage(270, 0);
                $image->writeImage($blogImageDir . $postId . ".jpg");
                $image->clear();
                $image->destroy();
            } finally {
                @unlink($filepath);
            }
        }

        $blogEntry = new BlogEntry($postId);
        $blogEntry->setTitle($_POST["title"] ?? '');

        if (empty($_POST["creator"])) {
            $blogEntry->setAuthor($user->getEmail());
        } else {
            $blogEntry->setAuthor($_POST["creator"]);
        }

        $blogEntry->setDate("en", $_POST["date_en"] ?? '');
        $blogEntry->setDate("gd", $_POST["date_gd"] ?? '');
        $blogEntry->setLexicopiaEntry($_POST["lexicopia_entry"] ?? '');
        $blogEntry->setContent("en", $_POST["content_en"] ?? '');
        $blogEntry->setContent("gd", $_POST["content_gd"] ?? '');
        $blogEntry->setPublishDate($_POST["publish_date"] ?? '');

        BlogEntries::saveBlogEntry($blogEntry);

        echo "<h3>Blog Entry Saved</h3>";
    }

    if (empty($_REQUEST["action"])) {
        $editListHtml = "<p>Or click on a blog entry to edit:</p><ul>";
        $ids = BlogEntries::getAllBlogEntryIds();

        foreach ($ids as $id) {
            $entry = BlogEntries::getBlogEntry($id);

            $idEsc = Functions::e((string)$id);
            $titleEsc = Functions::e($entry->getTitle());
            $publishEsc = Functions::e($entry->getPublishDate());

            $editListHtml .= <<<HTML
<li>
    <a href="?action=edit&amp;id={$idEsc}" title="Edit entry {$idEsc}">{$titleEsc}, {$publishEsc}</a>
</li>
HTML;
        }

        $editListHtml .= "</ul>";

        echo <<<HTML
<a href="?action=add" title="Add a blog entry">Add a new blog entry</a>
{$editListHtml}
HTML;
    } else {
        $author = "";

        if (isset($_GET["id"])) {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                Functions::writeError("Invalid blog entry ID.");
            }

            $blogEntry = BlogEntries::getBlogEntry($id);
            $author = $blogEntry->getAuthor();
        } else {
            $ids = BlogEntries::getAllBlogEntryIds();
            $id = empty($ids) ? 1 : (max($ids) + 1);
            $blogEntry = new BlogEntry($id);
        }

        //create the image HTML
        $blogId = (int)$blogEntry->getId();
        $blogIdEsc = Functions::e((string)$blogId);
        $imageHtml = file_exists($blogImageDir . $blogId . ".jpg")
            ? '<img src="/images/blog/' . $blogIdEsc . '.jpg" width="270" alt="Blog image">'
            : 'There is no image for the blog';

        $csrfField = Csrf::field();
        $title = Functions::e($blogEntry->getTitle());
        $date_en = Functions::e($blogEntry->getDate("en"));
        $date_gd = Functions::e($blogEntry->getDate("gd"));
        $content_en = Functions::e($blogEntry->getContent("en"));
        $content_gd = Functions::e($blogEntry->getContent("gd"));
        $publish_date = Functions::e($blogEntry->getPublishDate());
        $authorEsc = Functions::e($author);

        echo <<<HTML
<div class="blogAdmin">
    <a href="?" title="Blog Admin Main Menu">&lt;&lt; Main Menu</a>
    <br/><br/>

    <form id="blogPost" name="blogPost" method="POST" enctype="multipart/form-data">
        {$csrfField}

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="{$title}" required/>

        <br/><br/>

        <label for="date_en">Date (English):</label>
        <input type="text" id="date_en" name="date_en" value="{$date_en}" required/>

        <br/><br/>

        <label for="date_gd">Date (Gaelic):</label>
        <input type="text" id="date_gd" name="date_gd" value="{$date_gd}" required/>

        <br/><br/>

        <label for="content_en">Content (English):</label>
        <textarea name="content_en" id="content_en" rows="10" cols="80">{$content_en}</textarea>

        <br/><br/>

        <label for="content_gd">Content (Gaelic):</label>
        <textarea name="content_gd" id="content_gd" rows="10" cols="80">{$content_gd}</textarea>

        <br/><br/>

        <label for="publish_date">Publish date and time:</label>
        <input id="publish_date" name="publish_date" type="text" value="{$publish_date}" required/>

        <br/><br/>

        {$imageHtml}<br/><br/>

        <label for="image">Choose Image:</label>
        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp"/>

        <br/><br/>

        <input type="hidden" name="creator" value="{$authorEsc}"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id" value="{$blogIdEsc}"/>

        <input type="submit" value="save" class="dasg_bigButton"/>
    </form>
</div>
HTML;
    }
}

require_once 'includes/htmlFooter.php';
