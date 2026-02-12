<?php
declare(strict_types=1);

// ini_set("display_errors", 1);

session_start();

$javascriptBlock = <<<HTML
  <link rel="stylesheet" type="text/css" href="/datetimepicker/jquery.datetimepicker.css"/>
  <script src="/datetimepicker/jquery.datetimepicker.js"></script>
  <script type="text/javascript" src="/ckeditor/ckeditor.js"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('#newBlogPost').validate();
      jQuery('#publish_date').datetimepicker();
    });
  </script>
HTML;

$cqpPage = true;

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$pageSlug  = "blogAdmin";
$pageTitle = "DASG Blog Admin";

// Upload directory (filesystem)
$blogImageDir = rtrim(ROOT . "/images/blog/", DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

// Ensure upload dir exists and is writable
if (!is_dir($blogImageDir) || !is_writable($blogImageDir)) {
    http_response_code(500);
    exit("Blog image directory is not writable");
}

require_once 'includes/htmlHeader.php';

Functions::showLoginForm();

if (isset($_SESSION["user"])) {

    $user = Users::getUser($_SESSION["user"]);

    // check for blog admin status
    if ($user->getIsBlogAdmin() != 1) {
        Functions::writeError("You are not authorised to view this page");
    }

    echo "<a href=\"commentAdmin.php\" title=\"Comment Admin\">Comment Admin</a><br/><br/>";

    // save posted blog entry
    if (isset($_POST["id"])) {

        $id = (string)$_POST["id"];

        // process the image (SECURE REPLACEMENT)
        if (!empty($_FILES["image"]["tmp_name"]) && ($_FILES["image"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

            $f = $_FILES["image"];

            if (($f["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                Functions::writeError("Image upload failed (code " . (int)$f["error"] . ")");
            } elseif (!is_uploaded_file($f["tmp_name"])) {
                Functions::writeError("Invalid upload");
            } else {
                // Size limit (5MB) - adjust as desired
                if (($f["size"] ?? 0) > 5 * 1024 * 1024) {
                    Functions::writeError("Image is too large (max 5MB)");
                } else {
                    // MIME sniff (do NOT trust file extension / original name)
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime  = $finfo->file($f["tmp_name"]) ?: "";

                    $allowedMime = [
                        "image/jpeg" => "jpg",
                        "image/png"  => "png",
                        "image/gif"  => "gif",
                        "image/webp" => "webp",
                    ];

                    if (!isset($allowedMime[$mime])) {
                        Functions::writeError("Unsupported image type");
                    } else {
                        // Move to a temporary safe filename inside the target dir
                        $tmpName  = "upload_" . bin2hex(random_bytes(16)) . "." . $allowedMime[$mime];
                        $tmpPath  = $blogImageDir . $tmpName;

                        if (!move_uploaded_file($f["tmp_name"], $tmpPath)) {
                            Functions::writeError("Failed to store uploaded image");
                        } else {
                            try {
                                $image = new Imagick($tmpPath);

                                // Convert to JPEG thumbnail
                                $image->setImageFormat("jpeg");
                                $image->thumbnailImage(270, 0);

                                // Save final canonical filename based on entry id
                                $finalPath = $blogImageDir . $id . ".jpg";
                                $image->writeImage($finalPath);

                                @chmod($finalPath, 0644);
                            } catch (Throwable $e) {
                                Functions::writeError("Invalid image");
                            } finally {
                                // Remove temporary file
                                @unlink($tmpPath);
                            }
                        }
                    }
                }
            }
        }

        $blogEntry = new BlogEntry($_POST["id"]);
        $blogEntry->setTitle($_POST["title"]);
        if (empty($_POST["creator"]))
            $blogEntry->setAuthor($user->getEmail());
        else
            $blogEntry->setAuthor($_POST["creator"]);
        $blogEntry->setDate("en", $_POST["date_en"]);
        $blogEntry->setDate("gd", $_POST["date_gd"]);
        $blogEntry->setLexicopiaEntry($_POST["lexicopia_entry"]);
        $blogEntry->setContent("en", $_POST["content_en"]);
        $blogEntry->setContent("gd", $_POST["content_gd"]);
        $blogEntry->setPublishDate($_POST["publish_date"]);

        BlogEntries::saveBlogEntry($blogEntry);

        echo "<h3>Blog Entry Saved</h3>";
    }

    if (empty($_REQUEST["action"])) {
        $editListHtml = "<p>Or click on a blog entry to edit:</p><ul>";
        $ids = BlogEntries::getAllBlogEntryIds();
        foreach ($ids as $id) {
            $entry = BlogEntries::getBlogEntry($id);
            $editListHtml .= <<<HTML
        <li>
          <a href="?action=edit&id={$id}" title="Edit entry {$id}">{$entry->getTitle()}, {$entry->getPublishDate()}</a>
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
            $blogEntry = BlogEntries::getBlogEntry($_GET["id"]);
            $author = $blogEntry->getAuthor();
        }
        else {
            $ids = BlogEntries::getAllBlogEntryIds();
            $id = $ids[0]+1;
            $blogEntry = new BlogEntry($id);
        }

        // create the image HTML
        $imageHtml = (file_exists($blogImageDir . $blogEntry->getId() . ".jpg"))
            ? '<img src="/images/blog/' . $blogEntry->getId() . '.jpg" width="270px"/>'
            : 'There is no image for the blog';

        $csrfField = Csrf::field();

        $title = Functions::e($blogEntry->getTitle());
        $date_en = Functions::e($blogEntry->getDate("en"));
        $date_gd = Functions::e($blogEntry->getDate("gd"));
        $content_en = Functions::e($blogEntry->getContent("en"));
        $content_gd = Functions::e($blogEntry->getContent("gd"));
        $publish_date = Functions::e($blogEntry->getPublishDate());

        echo <<<HTML

    <div class="blogAdmin">

      <a href="?" title="Blog Admin Main Menu"><< Main Menu</a>
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
        <textarea name="content_en" id="content_en" rows="10" cols="80">
          {$content_en}
        </textarea>

        <br/><br/>

        <label for="content_gd">Content (Gaelic):</label>
        <textarea name="content_gd" id="content_gd" rows="10" cols="80">
          {$content_gd}
        </textarea>

        <script>
          CKEDITOR.replace('content_en');
          CKEDITOR.replace('content_gd');
        </script>

        <br/><br/>

        <label for="publish_date">Publish date and time:</label>
        <input id="publish_date" name="publish_date" type="text" value="{$publish_date}" required/>

        <br/><br/>

        {$imageHtml}<br/><br/>
        <label for="image">Choose Image:</label>
        <input type="file" name="image" id="image" accept="image/*"/>

        <br/><br/>

        <input type="hidden" name="creator" value="{$author}"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id" value="{$blogEntry->getId()}"/>

        <input type="submit" value="save" class="dasg_bigButton"/>

      </form>

    </div>

HTML;
    }
}

require_once 'includes/htmlFooter.php';