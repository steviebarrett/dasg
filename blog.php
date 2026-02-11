<?php


if (isset($_POST["action"]) && $_POST["action"] == "logout") {
	unset($_SESSION["user"]);	
}

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$pageSlug = "blog";
$pageTitle = "DASG Blog";

$javascriptBlock = <<<HTML

<!--link rel="stylesheet" type="text/css" href="/lexicopia/lexicopia.css"/>
<link rel="stylesheet" type="text/css" href="/lexicopia/table.css"/>
		
<script type="text/javascript" src="/lexicopia/wn.js"></script-->

<div id="fb-root"></div>
<script>

	$(document).ready(function() {
        
        $.ajaxSetup({
          headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
          }
        });
        
		$('#hideComments').on('click', function() {
			$('#hideComments').hide();
			$('#showComments').show();
			$('#commentsContainer').hide();
		}); 
		
		$('#showComments').on('click', function() {
			$('#showComments').hide();
			$('#hideComments').show();
			$('#commentsContainer').show();
		}); 
		
		$("#postComment").click(function(){
			if ($('#userComment').val() != '') { 
   	 			$.post("/ajax/blog.php?action=postComment",
    			{
        			id: $('#id').val(),
        			userComment: $('#userComment').val()
    			},
    			function(msg, status){
					$('#userComment').hide();
					$('#postComment').hide();
					$('#postingNotes').hide();
        			$('#commentSubmittedMsg').bPopup({
						fadeSpeed: 'slow', 
        	    		followSpeed: 1500
					});
    			});
			}
		});
	});
		
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.0";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	function getLexEntry(id)
	{
		var term = id.replace(/_/g, ' ');
		var html = "<h2>Lexicopia Entry for <em>" + term + "</em></h2><br/>";
		$.get("/lexicopia/newtable.php?lang=gd&id="+id, function(data) {	
			html += data;
			$('#lexPopup').html(html);
			$('#lexPopup').bPopup();
		});
	}
	
	function showMonth(monthId, ids, lang)
	{
		var url = "/ajax/blog.php?action=getMonthEntries&ids="+ids+"&lang="+lang;
		$("#selectedMonthContent").load(url);
		$(".blogMonth").removeClass("blogMonthBold");
		$("#"+monthId).addClass("blogMonthBold");
	}
</script>

HTML;

require_once 'includes/htmlHeader.php';

require_once 'includes/sideMenu.php';

$blogEntryId = empty($_REQUEST["id"]) ? BlogEntries::getMostRecentBlogEntryId() : $_REQUEST["id"];

$commentSubmitted = false;

if (isset($_POST["userComment"])) {

    $email = $_SESSION['user'] ?? '';
    if ($email === '') {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Not logged in']);
        exit;
    }
	
	$newCommentId = DB::getLastId(DB_NAME, "blogComment");
	$comment = new BlogComment($newCommentId);
	$comment->setBlogId($blogEntryId);
	$comment->setUserEmail($_SESSION["user"]);
	$comment->setApproved(0);
	$comment->setContent($_POST["userComment"]);
	$comment->setDate(date("Y-m-d H:i:s", time()));
	BlogComments::saveBlogComment($comment);
	$commentSubmitted = true;
}

$numRecentEntries = 4;

$blogView = new BlogView($blogEntryId, $lang, $numRecentEntries);

$blogHtml = $blogView->getHomeHtml($commentSubmitted);


echo <<<HTML
	
	<div id="lexPopup" class="popup"></div>
	<div class="pageContent">
		<div class="blogContent">
			{$blogHtml}
		</div>
	</div>
HTML;

			
require_once 'includes/htmlFooter.php';
