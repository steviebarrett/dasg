<?php
session_start();

require_once '../includes/include.php';


switch ($_GET["action"]) {
	
	case "getMonthEntries":
		$lang = $_GET["lang"];
		$ids = explode('|', $_GET["ids"]);
		foreach ($ids as $id) {
			$blogEntry = BlogEntries::getBlogEntry($id);
			$blogTitle = $blogEntry->getTitle();
			$blogFirstLine = $blogEntry->getFirstLine($lang);
			echo <<<HTML
				<div class="blogRecentPost">
					<a href="/blog/{$id}/{$lang}" title="{$blogTitle}">
						<img width="50px" alt="{$blogTitle}" src="/images/blog/{$id}.jpg"/>
						{$blogTitle}
						<p class="blogRecentPostDate">{$blogEntry->getDate($lang)}</p>
						<p>{$blogFirstLine}</p>
					</a>
				</div>	
HTML;
		}
		break;
	case "updateCommentStatus":
		$commentId = $_GET["id"];
		$status = ($_GET["status"] == "approve") ? 1 : -1;
		$comment = BlogComments::getBlogComment($commentId);
		$comment->setApproved($status);
		BlogComments::saveBlogComment($comment);
		break;
	case "postComment":
		if (!isset($_SESSION["user"])) {
			break;
		} 
		$comment = new BlogComment(-1);
		$comment->setBlogId($_POST["id"]);
		$comment->setContent(nl2br(strip_tags($_POST["userComment"])));
		$comment->setUserEmail($_SESSION["user"]);
		$comment->setDate(date("Y-m-d H:i:s", time()));
		$comment->setApproved(0);
		BlogComments::saveBlogComment($comment);
		echo "Your comment has been submitted for moderation";
		break;
	default:
		break;
}


