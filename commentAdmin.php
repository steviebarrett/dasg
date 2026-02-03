<?php

//ini_set("display_errors", 1);

session_start();

$javascriptBlock = <<<HTML

	<script type="text/javascript">
	
		function updateComment(id, status) {
			$.getJSON("/ajax/blog.php?action=updateCommentStatus&id="+id+"&status="+status);
			if (status == "approve") {
				$('#row_'+id).removeClass('commentRejected');
				$('#row_'+id).addClass('commentApproved');
			} else {
				$('#row_'+id).removeClass('commentApproved');
				$('#row_'+id).addClass('commentRejected');
			}
		}
		
	</script>
HTML;


$cqpPage = true;

require_once 'includes/include.php';

$pageSlug = "commentAdmin";
$pageTitle = "DASG Comment Admin";

require_once 'includes/htmlHeader.php';

if (Functions::showLoginForm() == true) {

	$user = Users::getUser($_SESSION["user"]);

	//check for blog admin status
	if ($user->getIsBlogAdmin() != 1) {
		Functions::writeError("You are not authorised to view this page");
	} 
	
	echo "<a href=\"blogAdmin.php\" title=\"Back to Blog Admin\">< Back to Blog Admin</a><br/><br/>";
	
	$unmodSelect = $rejectSelect = $approveSelect = "";
	if ($_GET["show"] == "rejected") {
		$rejectSelect = "selected=\"selected\"";
	} else if ($_GET["show"] == "approved") {
		$approveSelect = "selected=\"selected\"";
	} else {
		$unmodSelect = "selected=\"selected\"";
	}
	
	echo <<<HTML
		<form method="GET" action="commentAdmin.php">
			<select name="show">
				<option value="unmod" {$unmodSelect}>Awaiting Moderation</option>
				<option value="rejected" {$rejectSelect}>Rejected</option>
				<option value="approved" {$approveSelect}>Approved</option>
			</select>
			<input type="submit" class="dasg_medButton" value="Show"/>
		</form>

HTML;
	
	switch ($_GET["show"]) {
		case "rejected":
			echo "<h3>Rejected Comments</h3>";
			$rejectedCommentIds = BlogComments::getCommentIds(-1);
			if (count($rejectedCommentIds) == 0) {
				$tableHtml = "<h4>There are no rejected comments</h4>";
				break;
			}
			$tableHtml = getCommentTable($rejectedCommentIds, $showReject = false, $showApprove = true);
			break;
		case "approved":
			echo "<h3>Approved Comments</h3>";
			$approvedCommentIds = BlogComments::getCommentIds(1);
			if (count($approvedCommentIds) == 0) {
				$tableHtml = "<h4>There are no approved comments</h4>";
				break;
			}
			$tableHtml = getCommentTable($approvedCommentIds, $showReject = true, $showApprove = false);
			break;
		default: 	//unmoderated
			echo "<h3>Comments Awaiting Moderation</h3>";
			$unmodCommentIds = BlogComments::getCommentIds(0);
			if (count($unmodCommentIds) == 0) {
				$tableHtml = "<h4>There are no comments awaiting moderation</h4>";
				break;
			}
			$tableHtml = getCommentTable($unmodCommentIds, $showReject = true, $showApprove = true);
	}
	
	echo $tableHtml;
	
}



function getCommentTable($commentIds, $showReject, $showApprove) {
	$html = <<<HTML
		<table id="commentTable">
			<thead>
				<th>ID</th>
				<th>Comment</th>
				<th>Blog ID</th>
				<th>User</th>
				<th>Date</th>
				<th>Action</th>
			</thead>
			<tbody>
HTML;
	foreach ($commentIds as $commentId) {
		$comment = BlogComments::getBlogComment($commentId);
		$user = Users::getUser($comment->getUserEmail());
		$html .= <<<HTML
				<tr id="row_{$commentId}">
					<td>{$commentId}</td>
					<td>{$comment->getContent()}</td>
					<td><a href="/blog/{$comment->getBlogId()}/gd" target="_blank">{$comment->getBlogId()}</a></td>
					<td>{$user->getUsername()}</td>
					<td>{$comment->getDate()}</td>
					<td>
HTML;
		if ($showReject) {
			$html .= <<<HTML
						<input type="button" onclick="updateComment({$commentId}, 'reject');" class="dasg_smlButton" value="reject"/>
HTML;
		}
		if ($showApprove) {
			$html .= <<<HTML
						<input type="button" onclick="updateComment({$commentId}, 'approve');" class="dasg_medButton" value="approve"/>
HTML;
		}				
		$html .= <<<HTML
					</td>
				</tr>
HTML;
	}
	$html .= <<<HTML
			</tbody>
		</table>
HTML;
	return $html;
}

require_once 'includes/htmlFooter.php';
