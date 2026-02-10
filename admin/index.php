<?php

require_once '../includes/include.php';

$titleText = array("en"=>"Admin", "gd"=>"Admin");

$pageTitle = $titleText[$lang];
$pageSlug = "admin";

$cqpPage = true;
$javascriptBlock = <<<HTML
	<script src="../ckeditor/ckeditor.js"></script>
	<script>
		$('#textMetadata').validate();
	</script>
HTML;

require_once '../includes/htmlHeader.php';

if (Functions::showLoginForm() == true) {
    
    $user = Users::getUser($_SESSION["user"]);
    
    //check for blog admin status
    if ($user->getIsBlogAdmin() != 1) {
        Functions::writeError("You are not authorised to view this page");
    }
    
    if ($_POST["action"] == "save") {
        Metadata::saveMetadata($_POST);
        echo "<h2 class=\"error\">Text Saved</h2>";
        unset($_POST);
    }
    
    if ($_POST["reference_number"]) {
        
        if ($_POST["reference_number"] == -1) {
            $_POST["reference_number"] = "";
            $text = array();
        } else {
            $text = Metadata::getAllMetadata($_POST["reference_number"]);
        }
        
        $addToCorpusYes = "";
        $addToCorpusNo = "checked";
        if ($text["add_to_corpus"] == "y") {
            $addToCorpusYes = "checked";
            $addToCorpusNo = "";
        }
        
        //dateMacro options HTML
        $dateMacroHtml = "<option value=\"\">---select---</option>";
        $dateMacroOptions = array("12th c.", "16th c.", "17th c.", "Early 18th c.", "Mid 18th c.", "Late 18th c.",
            "Early 19th c.", "Mid 19th c.", "Late 19th c.", "Early 20th c.", "Mid 20th c.", "Late 20th c.",
            "Early 21st c.", "Various", "Unknown"
        );
        foreach ($dateMacroOptions as $date) {
            $selected = "";
            if ($date == $text["dateMacro"]) {
                $selected = "selected";
            }
            $dateMacroHtml .= <<<HTML
                <option value="{$date}" {$selected}>{$date}</option>
HTML;
        }
        
        //geoMacro options HTML
        $geoMacroHtml = "<option value=\"\">---select---</option>";
        $geoMacroOptions = array("Lewis", "Harris and N Uist", "Benbecula, S Uist and Barra",
            "N and W Sutherland", "W Ross", "Skye, Raasay and Small Isles", "W Inverness-shire",
            "Mull, Coll and Tiree", "Argyll mainland", "Islay, Jura, Kintyre and Arran",
            "E Ross, E Sutherland and Caithness", "E Inverness-shire", "Perthshire",
            "Canada", "Various", "Other", "Unknown"
        );
        foreach ($geoMacroOptions as $geo) {
            $selected = "";
            if ($geo == $text["geoMacro"]) {
                $selected = "selected";
            }
            $geoMacroHtml .= <<<HTML
                <option value="{$geo}" {$selected}>{$geo}</option>
HTML;
        }
        
        //medium options HTML
        $mediumHtml = "<option value=\"\">---select---</option>";
        $mediumOptions = array("Prose", "Verse", "Prose & Verse", "Conversation", "Other");
        foreach ($mediumOptions as $medium) {
            $selected = "";
            if ($medium == $text["medium"]) {
                $selected = "selected";
            }
            $mediumHtml .= <<<HTML
                <option value="{$medium}" {$selected}>{$medium}</option>
HTML;
        }
        
        //genre options HTML
        $genreHtml = "<option value=\"\">---select---</option>";
        $genreOptions = array("Literature", "Information", "Literature and Information");
        foreach ($genreOptions as $genre) {
            $selected = "";
            if ($genre == $text["genre"]) {
                $selected = "selected";
            }
            $genreHtml .= <<<HTML
                <option value="{$genre}" {$selected}>{$genre}</option>
HTML;
        }
        
        //the form
        $csrfField = Csrf::field();
        echo <<<HTML
			<div class="backLink">
				<a href="index.php" title="Back to Admin Home">< Back to Admin Home</a>
			</div>
			
			<form id="textMetadata" method="POST" action="index.php">
			
			    {$csrfField}
			    
				<div>
					<label for="reference_number">Reference Number</label>
					<input name="reference_number" id="reference_number" type="text" class="shortInput" value="{$_POST["reference_number"]}" required/>
				</div>
				<div>
					<label for="title">Title</label>
					<input name="title" id="title" type="text" value="{$text["title"]}" required/>
				</div>
				<div>
					<label for="author">Author</label>
					<input name="author" id="author" type="text" value="{$text["author"]}"/>
				</div>
				<div>
					<label for="editor">Editor</label>
					<input name="editor" id="editor" type="text" value="{$text["editor"]}"/>
				</div>
				<div>
					<label for="reference_author">Reference Author</label>
					<input name="reference_author" id="reference_author" type="text" value="{$text["reference_author"]}"/>
				</div>
				<div>
					<label for="reference_editor">Reference Editor</label>
					<input name="reference_editor" id="reference_editor" type="text" value="{$text["reference_editor"]}"/>
				</div>
				<div>
					<label for="reference_volume">Reference Volume</label>
					<input name="reference_volume" id="reference_volume" type="text" value="{$text["reference_volume"]}"/>
				</div>
				<div>
					<label for="date_of_edition">Date of Edition</label>
					<input name="date_of_edition" id="date_of_edition" type="text" class="shortInput" value="{$text["date_of_edition"]}"/>
				</div>
				<div>
					<label for="date_of_language">Date of Language</label>
					<input name="date_of_language" id="date_of_language" type="text" class="mediumInput" value="{$text["date_of_language"]}"/>
				</div>
                <div>
                    <label for="dateMacro">DateMacro</label>
                    <select name="dateMacro" id="dateMacro">
                        {$dateMacroHtml}
                    </select>
                </div>
				<div>
					<label for="date_of_language_ed">Date of Language (Public)</label>
					<input name="date_of_language_ed" id="date_of_language_ed" type="text" class="shortInput" value="{$text["date_of_language_ed"]}"/>
				</div>
                <div>
					<label for="date_of_language_notes">Date of Language Notes</label>
					<textarea name="date_of_language_notes" cols="40" id="date_of_language_notes">{$text["date_of_language_notes"]}</textarea>
					<script>CKEDITOR.replace("date_of_language_notes");</script>
				</div>
				<div>
					<label for="publisher">Publisher</label>
					<input name="publisher" id="publisher" type="text" value="{$text["publisher"]}"/>
				</div>
				<div>
					<label for="place_published">Place Published</label>
					<input name="place_published" id="place_published" type="text" value="{$text["place_published"]}"/>
				</div>
				<div>
					<label for="volume">Volume</label>
					<input name="volume" id="volume" type="text" class="mediumInput" value="{$text["volume"]}"/>
				</div>
				<div>
					<label for="location">Location</label>
					<input name="location" id="location" type="text" value="{$text["location"]}"/>
				</div>
				<div>
					<label for="link">Link</label>
					<input name="link" id="link" type="text" value="{$text["link"]}"/>
				</div>
				<div>
					<label for="link_label">Link Label</label>
					<input name="link_label" id="link_label" type="text" value="{$text["link_label"]}"/>
				</div>
				<div>
					<label for="download_file">Download File</label>
					<input name="download_file" id="download_file" type="text" value="{$text["download_file"]}"/>
				</div>
				<div>
					<label for="geographical_origins">Geographical Origins</label>
					<input name="geographical_origins" id="geographical_origins" type="text" value="{$text["geographical_origins"]}"/>
				</div>
				<div>
					<label for="geographical_origins_ed">Geographical Origins (Public)</label>
					<input name="geographical_origins_ed" id="geographical_origins_ed" type="text" value="{$text["geographical_origins_ed"]}"/>
				</div>
                <div>
					<label for="geographical_origins_notes">Geographical Origins Notes</label>
					<textarea name="geographical_origins_notes" cols="40" id="geographical_origins_notes">{$text["geographical_origins_notes"]}</textarea>
					<script>CKEDITOR.replace("geographical_origins_notes");</script>
				</div>
                <div>
                    <label for="geoMacro">GeoMacro</label>
                    <select name="geoMacro" id="geoMacro">
                        {$geoMacroHtml}
                    </select>
                </div>
                <div>
					<label for="geoX">Geo X co-ord</label>
					<input name="geoX" id="geoX" type="text" value="{$text["geoX"]}"/>
				</div>
                <div>
					<label for="geoY">Geo Y co-ord</label>
					<input name="geoY" id="geoY" type="text" value="{$text["geoY"]}"/>
				</div>
				<div>
					<label for="register">Register</label>
					<input name="register" id="register" type="text" value="{$text["register"]}"/>
				</div>
				<div>
					<label for="register_ed">Register (Public)</label>
					<input name="register_ed" id="register_ed" type="text" value="{$text["register_ed"]}"/>
				</div>
				<div>
                    <label for="medium">Medium</label>
                    <select name="medium" id="medium">
                        {$mediumHtml}
                    </select>
                </div>
                <div>
                    <label for="genre">Genre</label>
                    <select name="genre" id="genre">
                        {$genreHtml}
                    </select>
                </div>
                <div>
					<label for="numWords">Word Count</label>
					<input name="numWords" id="numWords" type="text" class="shortInput" value="{$text["numWords"]}"/>
				</div>
				<div>
					<label for="reference_style">Reference Style</label>
					<textarea name="reference_style" cols="40" id="reference_style">{$text["reference_style"]}</textarea>
					<script>CKEDITOR.replace("reference_style");</script>
				</div>
				<div>
					<label for="rating">Rating</label>
					<textarea name="rating" id="rating">{$text["rating"]}</textarea>
					<script>CKEDITOR.replace("rating");</script>
				</div>
				<div>
					<label for="alternative_author_name">Alternative Author Name</label>
					<input name="alternative_author_name" id="alternative_author_name" type="text" value="{$text["alternative_author_name"]}"/>
				</div>
				<div>
					<label for="manuscript_or_edition">Manuscript or Edition</label>
					<input name="manuscript_or_edition" id="manuscript_or_edition" type="text" value="{$text["manuscript_or_edition"]}"/>
				</div>
				<div>
					<label for="size_and_condition">Size and Condition</label>
					<textarea name="size_and_condition" id="size_and_condition">{$text["size_and_condition"]}</textarea>
					<script>CKEDITOR.replace("size_and_condition");</script>
				</div>
				<div>
					<label for="short_title">Short Title</label>
					<input name="short_title" id="short_title" type="text" value="{$text["short_title"]}" required/>
				</div>
				<div>
					<label for="reference_details">Reference Details</label>
					<input name="reference_details" id="reference_details" type="text" value="{$text["reference_details"]}"/>
				</div>
				<div>
					<label for="number_of_pages">Number of Pages</label>
					<textarea name="number_of_pages" id="number_of_pages">{$text["number_of_pages"]}</textarea>
					<script>CKEDITOR.replace("number_of_pages");</script>
				</div>
				<div>
					<label for="gaelic_text_by">Gaelic Text By</label>
					<input name="gaelic_text_by" id="gaelic_text_by" type="text" value="{$text["gaelic_text_by"]}"/>
				</div>
				<div>
					<label for="illustrator">Illustrator</label>
					<input name="illustrator" id="illustrator" type="text" value="{$text["illustrator"]}"/>
				</div>
				<div>
					<label for="social_context">Social Context</label>
					<textarea name="social_context" id="social_context">{$text["social_context"]}</textarea>
					<script>CKEDITOR.replace("social_context");</script>
				</div>
				<div>
					<label for="contents">Contents</label>
					<textarea name="contents" id="contents">{$text["contents"]}</textarea>
					<script>CKEDITOR.replace("contents");</script>
				</div>
				<div>
					<label for="sources">Sources</label>
					<textarea name="sources" id="sources">{$text["sources"]}</textarea>
					<script>CKEDITOR.replace("sources");</script>
				</div>
				<div>
					<label for="text_language">Language</label>
					<textarea name="language" id="text_language">{$text["language"]}</textarea>
					<script>CKEDITOR.replace("text_language");</script>
				</div>
				<div>
					<label for="orthography">Orthography</label>
					<textarea name="orthography" id="orthography">{$text["orthography"]}</textarea>
					<script>CKEDITOR.replace("orthography");</script>
				</div>
				<div>
					<label for="edition">Edition</label>
					<textarea name="edition" id="edition">{$text["edition"]}</textarea>
					<script>CKEDITOR.replace("edition");</script>
				</div>
				<div>
					<label for="other_sources">Other Sources</label>
					<textarea name="other_sources" id="other_sources">{$text["other_sources"]}</textarea>
					<script>CKEDITOR.replace("other_sources");</script>
				</div>
				<div>
					<label for="further_reading">Further Reading</label>
					<textarea name="further_reading" id="further_reading">{$text["further_reading"]}</textarea>
					<script>CKEDITOR.replace("further_reading");</script>
				</div>
				<div>
					<label for="credits">Credits</label>
					<textarea name="credits" id="credits">{$text["credits"]}</textarea>
					<script>CKEDITOR.replace("credits");</script>
				</div>
				<div>
					<label for="filename">Filename</label>
					<input name="filename" id="filename" type="text" value="{$text["filename"]}"/>
				</div>
				<div>
					<label for="add_to_corpus">Add to Corpus</label>
					y<input name="add_to_corpus" id="add_to_corpus_y" type="radio" value="y" {$addToCorpusYes}/>&nbsp;
					n<input name="add_to_corpus" id="add_to_corpus_n" type="radio" value="n" {$addToCorpusNo}/>
				</div>
				
				<!--div>
					<label for="imported">Imported</label>
					<input name="imported" id="imported" type="text" class="shortInput" value="{$text["imported"]}"/>
				</div-->
				
				<div>
					<input type="hidden" name="action" value="save"/>
					<input type="submit" class="dasg_bigButton" value="save">
				</div>
				
			</form>
			
			<a href="index.php" title="Back to Admin Home">< Back to Admin Home</a>
HTML;
                        
    } else {								//no text selected, show the list
        
        $texts = Metadata::getAllTextShortTitles();
        $formHtml = "<option selected=\"selected\" value=\"-1\">-- Add a new text --</option>";
        foreach ($texts as $reference_number=>$short_title) {
            $formHtml .= <<<HTML
				<option value="{$reference_number}">{$reference_number} - {$short_title}</option>
HTML;
        }

        $csrfField = Csrf::field();
        echo <<<HTML
			<h3>Add/Edit Text:</h3>
			<form method="POST" action="index.php">
			    {$csrfField}
				<select name="reference_number">
					{$formHtml}
				</select>
				<input type="submit" value="Go"/>
			</form>
			<br/>
			<a href="slips.php" title="Slips Admin">Slips Admin</a>
			<br/><br/>
			<a href="gairm.php" title="Gairm Admin">Gairm Admin</a>
HTML;
					
                        }
}

require_once '../includes/htmlFooter.php';
