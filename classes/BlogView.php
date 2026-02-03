<?php

class BlogView {
	
	private $blogEntryId, $lang, $numRecentEntries, $showUnpublished;
	
	private $monthNames = array(
		"en"=>array(
			1=>"Jan", 2=>"Feb", 3=>"Mar", 4=>"Apr", 5=>"May", 6=>"Jun", 7=>"Jul", 8=>"Aug", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dec"
		), 
		"gd"=>array(
			1=>"Faoi", 2=>"Gear", 3=>"Màrt", 4=>"Gibl", 5=>"Cèit", 6=>"Ògmh", 7=>"Iuch", 8=>"Lùna", 9=>"Sult", 10=>"Dàmh", 11=>"Samh", 12=>"Dùbh"
		)
	);
	
	public function __construct($blogEntryId, $lang, $numRecentEntries, $showUnpublished=false) {
		$this->blogEntryId 		= $blogEntryId;
		$this->lang				= $lang;
		$this->numRecentEntries	= $numRecentEntries;
		$this->showUnpublished	= $showUnpublished;
	}
	
	public function getHomeHtml($commentSubmitted = false) {
		$html = $this->getEntryHtml();
		$html .= $this->getCommentHtml($commentSubmitted);
		$html .= $this->getMostRecentHtml();
		$html .= $this->getArchiveHtml();
		return $html;
	}
	
	private function getEntryHtml() {
		$blogEntry = BlogEntries::getBlogEntry($this->blogEntryId);
	
		if (!$this->showUnpublished && !$blogEntry->getIsPublished())	{ //only show entries that are published
			return;		
		}
		
		$author = Users::getUser($blogEntry->getAuthor());
		
		$byLine = array(
			"en"=>"Posted by {$author->getFirstName()} on {$blogEntry->getDate("en")}",
			"gd"=>"Air a sgrìobhadh le {$author->getFirstName()} air {$blogEntry->getDate("gd")}"
		);
		
		/*
		 * Lexicopia code
		 */
		$lexHtml = "";
		/*
		if ($lexEntry = $blogEntry->getLexicopiaEntry()) {
			
			$lexLine = array(
				"en"=>'<a href="javascript:getLexEntry(\'' . $lexEntry . '\');">View lexicopia entry</a>',
				"gd"=>'<a href="javascript:getLexEntry(\'' . $lexEntry . '\');">View lexicopia entry</a>'
			);
			$lexHtml = "<br/><br/>{$lexLine[$lang]}<br/>";
		}
		*/
		//get next and prev HTML
		$nextAndPrevIds = BlogEntries::getNextAndPrevIds($this->blogEntryId);
		$prevText = array("en"=>"Previous", "gd"=>"Air ais");
		$nextText = array("en"=>"Next", "gd"=>"Air adhart");
		$nextTitle = $prevTitle = "";
		if (isset($nextAndPrevIds["next"])) {
			$nextBlog = BlogEntries::getBlogEntry($nextAndPrevIds["next"]);
			$nextTitle = $nextBlog->getTitle();
			$nextHtml = <<<HTML
				{$nextText[$this->lang]} >
HTML;
		}
		if (isset($nextAndPrevIds["prev"])) {
			$prevBlog = BlogEntries::getBlogEntry($nextAndPrevIds["prev"]);
			$prevTitle = $prevBlog->getTitle();
			$prevHtml = <<<HTML
				< {$prevText[$this->lang]}
HTML;
		}
		
		$blogHtml = <<<HTML
			<div class="blogEntry">
			
				<div class="blogSidebar">
					<img width="270px" alt="{$blogEntry->getTitle()}" src="/images/blog/{$this->blogEntryId}.jpg">
				</div>
				
				<a id="{$this->blogEntryId}"></a>
	
				<h3>{$blogEntry->getTitle()}</h3>
				
				<div class="blogDate">
					<em>{$byLine[$this->lang]}</em>
					{$lexHtml}
				</div>
				
				{$blogEntry->getContent($this->lang)}
				
				<div id="blogNavContainer">
					<div id="prevBlog"><a href="/blog/{$nextAndPrevIds["prev"]}/{$this->lang}" title="{$prevTitle}">{$prevHtml}</a></div>
					<div id="nextBlog"><a href="/blog/{$nextAndPrevIds["next"]}/{$this->lang}" title="{$nextTitle}">{$nextHtml}</a></div>
				</div>
			</div>

HTML;

		return $blogHtml;
	}
	
	private function getCommentHtml($commentSubmitted) {
		$html = "";
		$user = Users::getUser($_SESSION["user"]);
		$hideCommentsText["en"] = "Hide Comments";
		$hideCommentsText["gd"] = "Falaich beachdan";
		$showCommentsText["en"] = "Show Comments";
		$showCommentsText["gd"] = "Foillsich beachdan";
		$messageHtml["en"] = "Your comment has been submitted for moderation";
		$messageHtml["gd"] = "Chaidh ur beachd a chur a-staigh gus sgrùdadh a dhèanamh air";
		$message = $messageHtml[$this->lang];
		$postingNotesText["en"] = "All posts are moderated. Please do not use any HTML tags.";
		$postingNotesText["gd"] = "Thèid sgrùdadh a dhèanamh air a h-uile post. Na cuiribh tagaichean HTML gu feum mas e ur toil e.";

		$html .= <<<HTML
			<input type="button" class="dasg_bigButton" id="hideComments" value="{$hideCommentsText[$this->lang]}"/> 
			<div id="commentSubmittedMsg">{$message}</div>
			<input type="button" class="dasg_bigButton" id="showComments" value="{$showCommentsText[$this->lang]}"/>
			
			<div id="commentsContainer">
HTML;
		
		if ($user) {
			$placeholderText["en"] = "Add your comment here ...";
			$placeholderText["gd"] = "Faodar beachd a chur a-staigh an seo";
 			$postCommentText["en"] = "Post Comment";
 			$postCommentText["gd"] = "Cuir beachd a-staigh";
			$html .= <<<HTML
					<form action="#hideComments" method="POST">
						<textarea id="userComment" placeholder="{$placeholderText[$this->lang]}" name="userComment"></textarea>
					
						<input type="hidden" id="id" name="id" value="{$this->blogEntryId}"/>
						
						<input type="button" class="dasg_bigButton" id="postComment" name="postComment" value="{$postCommentText[$this->lang]}"/>
					</form> 
					<p id="postingNotes">{$postingNotesText[$this->lang]}</p>
HTML;
		}
		//comment section
		$commentIds = BlogComments::getBlogCommentIds($this->blogEntryId);
		//no comments - return a simple message
		$numComments = count($commentIds);
		if ($numComments == 0) {
			$html .= ($this->lang == "en") ? "<h5>There are no comments for this post</h5>" : "<h5>Chan eil beachdan rim faotainn airson a’ phuist seo</h5>"; 
		} else {
			//comments found
			$html .= <<<HTML
				<ul id="postedComments">
HTML;
			foreach ($commentIds as $commentId) {
				$commentNum = $numComments--;
				$comment = BlogComments::getBlogComment($commentId);
				$user = Users::getUser($comment->getUserEmail());
				$html .= <<<HTML
					<li>
						<h5>{$user->getUsername()}</h5>
						<p>{$comment->getContent()}</p>
						<em class="date">{$comment->getDate()}</em>
					</li>
HTML;
			}
			$html .= <<<HTML
				</ul>
HTML;
		}
		
		
		//login display
		$html .= <<<HTML
			<div id="blogLogin">	
HTML;
		if (empty($_SESSION["user"])) {
			$loginRegisterHtml["en"] = <<<HTML
				<a href="/login.php?lang=en&section=hideComments" title="Login to DASG">Login</a> or
				<a href="/register.php?lang=en" title="Register for DASG">Register</a>
HTML;
			$loginRegisterHtml["gd"] = <<<HTML
				<a href="/login.php?lang=gd&section=hideComments" title="Cuir a-staigh">Cuir a-staigh</a>&nbsp;
				<a href="/register.php?lang=gd" title="Cunntas a chruthachadh">Cunntas a chruthachadh</a>
HTML;
					$html .= $loginRegisterHtml[$this->lang];
		} else {
			$user = Users::getUser($_SESSION["user"]);
			$logoutLabel["en"] = "logout";
			$logoutLabel["gd"] = "Cuir dheth"; 
			$logoutHtml = <<<HTML
					<form method="POST" action="">
					<input type="hidden" name="action" value="logout"/>
					<input class="dasg_medButton" type="submit" value="{$logoutLabel[$this->lang]}"/>
				</form>
HTML;
			$loggedInHtml["en"] = "Logged in as {$user->getFirstName()} {$user->getLastName()}";
			$loggedInHtml["gd"] = "Clàraichte a-staigh mar  {$user->getFirstName()} {$user->getLastName()}";
						
			$html .= $loggedInHtml[$this->lang] . $logoutHtml;
		}
		$html .= "</div></div>";
		return $html;
	}
	
	private function getMostRecentHtml() {
		$mostRecentIds = BlogEntries::getMostRecentBlogEntryIds($this->numRecentEntries+1);	//add an extra entry in case current entry is included
		if (($key = array_search($this->blogEntryId, $mostRecentIds)) !== false) {
			unset($mostRecentIds[$key]);			//remove the current blog entry if found ...
		} else {
			$key = array_pop($mostRecentIds);		//... or remove the extra blog entry at the end 
		}
		$mostRecentText["en"] = "Most Recent Posts";
		$mostRecentText["gd"] = "Na puist mu dheireadh";
		$html = "<div id=\"recentPostsContainer\"><h4>{$mostRecentText[$this->lang]}</h4>";
		foreach ($mostRecentIds as $id) {
			$blogEntry = BlogEntries::getBlogEntry($id);
			$blogTitle = $blogEntry->getTitle();
			$blogFirstLine = $blogEntry->getFirstLine($this->lang);
			$html .= <<<HTML
				<div class="blogRecentPost">
					<a href="/blog/{$id}/{$this->lang}" title="{$blogTitle}">
						<img width="50px" alt="{$blogTitle}" src="/images/blog/{$id}.jpg"/>
						<div>
							{$blogTitle}
							<p class="blogRecentPostDate">{$blogEntry->getDate($this->lang)}</p>
							<p>{$blogFirstLine}</p>
						</div>
					</a>
				</div>		
HTML;
		}
		$html .= "<br class=\"clear\"/></div>";
		return $html;
	}

	private function getArchiveHtml() {
		$archivedText["en"] = "Archived Posts";
		$archivedText["gd"] = "Na puist tasgaichte";
		$html = <<<HTML
			<div id="archivedPostsContainer">
				<h4>{$archivedText[$this->lang]}</h4>
				<div id="blogArchiveMenu">
HTML;

		$archiveIds = BlogEntries::getBlogEntryIdsByDate();
		foreach ($archiveIds as $year => $month) {
			$html .= "<h5>{$year}</h5><ul class=\"blogArchiveList\">";
			foreach ($month as $m => $id) {
				$ids = implode('|', $id);
				$html .= <<<HTML
					<li>
						<a href="#" id="{$year}{$m}" class="blogMonth" onclick="showMonth('{$year}{$m}', '{$ids}', '{$this->lang}');return false;">{$this->monthNames[$this->lang][$m]}</a>
					</li>
HTML;
			} 
			$html .= "</ul>";
		}
		$html .= <<<HTML
				</div>
				<div id="selectedMonthContent"></div>
			</div>
HTML;
		return $html;
	}
	
}