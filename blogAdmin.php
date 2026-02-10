<?php 

//ini_set("display_errors", 1);

session_start();

$javascriptBlock = <<<HTML

	<link rel="stylesheet" type="text/css" href="/datetimepicker/jquery.datetimepicker.css"/ >
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
	
	echo "<a href=\"commentAdmin.php\" title=\"Comment Admin\">Comment Admin</a><br/><br/>";
	
	//save posted blog entry
	if ($_POST["id"]) {
			
		//process the image
		if (!empty($_FILES["image"]["name"])) {
			$filepath = $blogImageDir . $_FILES['image']['name'];
			move_uploaded_file ($_FILES['image']['tmp_name'], $filepath);
			$image = new Imagick($filepath);
			$image->setImageFormat("jpeg");
			$image->thumbnailImage(270, 0);
			$image->writeImage($blogImageDir . $_POST["id"] . ".jpg");
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
		
		if ($_GET["id"]) {
			$blogEntry = BlogEntries::getBlogEntry($_GET["id"]);
			$author = $blogEntry->getAuthor();
		}
		else {
			$ids = BlogEntries::getAllBlogEntryIds();
			$id = $ids[0]+1;
			$blogEntry = new BlogEntry($id);
		}
		
		//create the image HTML
		$imageHtml = (file_exists($blogImageDir . $blogEntry->getId() . ".jpg")) ? '<img src="/images/blog/' . $blogEntry->getId() . '.jpg" width="270px"/>' : 'There is no image for the blog';	
		echo <<<HTML
		
			<div class="blogAdmin">
			
				<a href="?" title="Blog Admin Main Menu"><< Main Menu</a>
				<br/><br/>
				
				<form id="blogPost" name="blogPost" method="POST" enctype="multipart/form-data">
				
					<label for="title">Title:</label>
					<input type="text" id="title" name="title" value="{$blogEntry->getTitle()}" required/>
					
					<br/><br/>
					
					<label for="date_en">Date (English):</label>
					<input type="text" id="date_en" name="date_en" value="{$blogEntry->getDate("en")}" required/>
					
					<br/><br/>
					
					<label for="date_gd">Date (Gaelic):</label>
					<input type="text" id="date_gd" name="date_gd" value="{$blogEntry->getDate("gd")}" required/>
					
					<br/><br/>
					
					<!--label for="lexicopia_entry">Lexicopia ID:</label>
					<input type="text" id="lexicopia_entry" name="lexicopia_entry" value="{$blogEntry->getLexicopiaEntry()}"/-->
					
					<br/><br/>
					
					<label for="content_en">Content (English):</label>
					<textarea name="content_en" id="content_en" rows="10" cols="80">
						{$blogEntry->getContent("en")}
			        </textarea>
			        
			        <br/><br/>
			        
			        <label for="content_gd">Content (Gaelic):</label>
					<textarea name="content_gd" id="content_gd" rows="10" cols="80">
						{$blogEntry->getContent("gd")}
			        </textarea>
			        
			        <script>
			        	CKEDITOR.replace('content_en');
			        	CKEDITOR.replace('content_gd');
			        </script>
			        
			        <br/><br/>
			        
			        <label for="publish_date">Publish date and time:</label>
			        <input id="publish_date" name="publish_date" type="text" value="{$blogEntry->getPublishDate()}" required/>
			        
			        <br/><br/>
			        
			        {$imageHtml}<br/><br/>
			        <label for="image">Choose Image:</label>
			        <input type="file" name="image" id="image"/>
			        
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

