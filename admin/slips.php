<?php

require_once '../includes/include.php';

$titleText = array("en"=>"Slips Admin", "gd"=>"Slips Admin");

$pageTitle = $titleText[$lang];
$pageSlug = "slipsAdmin";

$cqpPage = true;
$javascriptBlock = <<<HTML
	<script src="../ckeditor/ckeditor.js"></script>
	<script>
		$(function() {
			$('#folder').change(function() {
				$('#selectSlip').empty();
				var folder = $(this).val();
				if (folder === 'showAll') {
					folder = '';
				}
				$('#selectSlip').append('<option value="-1">-- Add a new slip --</option>');
				$.getJSON('../ajax/slips.php?action=getSlips&folder=' + folder, function(data) {
					$.each(data, function(key, val) {
						$('#selectSlip').append('<option value="' + key + '">' + key + ' - ' + val + '</option>');
					});
				});
			});
		});
	</script>
HTML;

require_once '../includes/htmlHeader.php';

if (Functions::showLoginForm() == true) {

    $csrfField = Csrf::field();

	$user = Users::getUser($_SESSION["user"]);
	
	//check for admin status
	if ($user->getIsBlogAdmin() != 1) {
		Functions::writeError("You are not authorised to view this page");
	}
	
	if ($_POST["action"] == "save") {
		MssSlips::saveSlip($_POST);
		echo "<h2 class=\"error\">Slip Saved</h2>";
		unset($_POST);
	}
	
	if (isset($_POST["id"])) {
		
		if ($_POST["id"] == -1) {
			$_POST["id"] = "";
			$slip = array();
			$slip["folder"] = $_POST["folder"];
		} else {
			$slip = MssSlips::getSlip($_POST["id"]);
		}
		
		//check slip found status for checkbox
		$slipFoundChecked = ($slip["slip_found"] == 1) ? "checked" : "";
		
		//assemble folder select html
		$folders = MssSlips::getAllFolders();
		$folderOptionHtml = "";
		foreach ($folders as $sortOrder => $folderName) {
			$selected = ($folderName === $slip["folder"]) ? "selected" : "";
			$folderOptionHtml .= '<option value="' . $folderName . '"' . $selected . '>' . $folderName . '</option>';
		}
		$folderSelectHtml = <<<HTML
			<select name="folder" id="folder">
				{$folderOptionHtml}
			</select>
HTML;
				//assemble date range html
				$dateRangeHtml = <<<HTML
			<select name="dateRange[]" id="nameRange" multiple size="11">
HTML;
				$dateRanges = MssSlips::getAllDateRanges();
				$slipDateRanges = MssSlips::getSlipDateRanges($slip["id"]);
				foreach ($dateRanges as $id => $dateRange) {
					$selected = (in_array($id, $slipDateRanges)) ? "selected" : "";
					$dateRangeHtml .= "<option value=\"{$id}\" {$selected}>{$dateRange}</option>";
				}
				$dateRangeHtml .= "</select>";

                //the form

				echo <<<HTML

                    {$csrfField}
                    <div class="backLink">
                        <a href="slips.php" title="Back to Slips Home">< Back to Slips Home</a>
                    </div>
                    
                    <form id="slipData" method="POST">
                    
                        <div>
                            <label for="folder">Folder</label>
                            {$folderSelectHtml}
                        </div>
                        <div>
                            <label for="id">Slip ID</label>
                            <input name="id" id="id" type="text" class="shortInput" value="{$_POST["id"]}"/>
                        </div>
                        <div>
                            <label for="headword">Headword</label>
                            <input name="headword" id="headword" type="text" value="{$slip["headword"]}" required/>
                        </div>
                        <div>
                            <label for="slip_found">Slip Found</label>
                            <input name="slip_found" id="slip_found" type="checkbox" value="1" {$slipFoundChecked}/>
                        </div>
                        <div>
                            <label for="quotation">Quotation</label>
                            <textarea name="quotation" id="quotation">{$slip["quotation"]}</textarea>
                            <script>CKEDITOR.replace("quotation");</script>
                        </div>
                        <div>
                            <label for="author">Author</label>
                            <input name="author" id="author" type="text" value="{$slip["author"]}"/>
                        </div>
                        <div>
                            <label for="title">Title</label>
                            <input name="title" id="title" type="text" value="{$slip["title"]}"/>
                        </div>
                        <div>
                            <label for="page">Page</label>
                            <input name="page" id="page" type="text" class="shortInput" value="{$slip["page"]}"/>
                        </div>
                        <div>
                            <label for="date">Date</label>
                            <input name="date" id="date" type="text" class="shortInput" value="{$slip["date"]}"/>
                        </div>
                        
                        <div>
                            <label for="dateRange">Date Range</label>
                            {$dateRangeHtml}
                        </div>
                        
                        <div>
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes">{$slip["notes"]}</textarea>
                            <script>CKEDITOR.replace("notes");</script>
                        </div>
                        <div>
                            <label for="translation">Translation</label>
                            <textarea name="translation" id="translation">{$slip["translation"]}</textarea>
                            <script>CKEDITOR.replace("translation");</script>
                        </div>
                        <div>
                            <label for="sense">Sense</label>
                            <input name="sense" id="sense" type="text" value="{$slip["sense"]}"/>
                        </div>
                        <div>
                            <label for="edition">Edition</label>
                            <input name="edition" id="edition" type="text" value="{$slip["edition"]}"/>
                        </div>
                                    
                        <div>
                            <input type="hidden" name="action" value="save"/>
                            <input type="submit" class="dasg_bigButton" value="save">
                        </div>
                                    
                    </form>
                                    
                    <a href="slips.php" title="Back to Slips Home">< Back to Slips Home</a>
							
HTML;
					
	} else {								//no slip selected, show the list
		
		//assemble folder select html
		$folders = MssSlips::getAllFolders();
		$folderOptionHtml = <<<HTML
			<option value="showAll">-- Show All Slips --</option>
HTML;
		foreach ($folders as $sortOrder => $folderName) {
			$folderOptionHtml .= '<option value="' . $folderName . '">' . $folderName . '</option>';
		}
		$folderSelectHtml = <<<HTML
			<label for="folder">Folder</label>
			<select name="folder" id="folder">
				{$folderOptionHtml}
			</select>
HTML;
				
				$slips = MssSlips::getAllHeadwords();
				$formHtml = "<option selected=\"selected\" value=\"-1\">-- Add a new slip --</option>";
				foreach ($slips as $id=>$headword) {
					$formHtml .= <<<HTML
				<option value="{$id}">{$id} - {$headword}</option>
HTML;
				}
				
				echo <<<HTML
			<h3>Add/Edit Slip:</h3>
			
			<form method="POST" action="slips.php">
			
			    {$csrfField}
			    
				{$folderSelectHtml}
				<label for="selectSlip">Slip</label>
				<select name="id" id="selectSlip">
					{$formHtml}
				</select>
				<input type="submit" value="Go"/>
			</form>
							
			<a href="index.php" title="Back to Admin Home" id="manageSlipsLink">Back to Admin Home</a>
HTML;
					}
}

echo <<<JS
	<script>
		$('#slipData').validate({
			rules: {
				id: {
					required: true,
					minlength: 7,
					maxlength: 7
				}
			}
		});
	</script>
JS;

require_once '../includes/htmlFooter.php';