<?php


namespace views;
use models;

class record
{
	private $_record;
	private $_origin;

	//an array of fields with controlled vocabularies
	private $_controlOptions = array(
		"classifications"=>array(
			"Ballad", "Bawdy", "Clapping", "Complaint","Dialogue", "Drinking","Elegy", "Exile", "Flyting", "Historical",
			"Homeland", "Humorous","Instructive", "Lament","Local events and characters", "Love","Lullaby", "Macaronic",
			"Milling", "Nature","Pibroch", "Political","Port-a-beul", "Praise","Rann / Duan", "Religious","Sailing",
			"Satire","Spiritual", "Supernatural","Work"),
		"structure"=>array(
			"One line verse", "One line verse / Three line Chorus", "One line verse / split chorus", "Two line verse",
			"Two line verse / Two line chorus", "Two line verse / Three line chorus", "Two line verse / Four line chorus",
			"Two line verse / Woven", "Three line verse", "Three line verse / Two line chorus",
			"Three line verse / Three line chorus", "Three line verse / Four line chorus", "Three line verse / Woven",
			"Four line verse", "Four line verse / Two line chorus", "Four line verse / Three line chorus",
			"Four line verse / Four line chorus", "Four line verse / Five line chorus", "Five line verse", "Six line verse",
			"Six line verse / Two line chorus", "Six line verse / Three line chorus", "Six line verse / Four line chorus",
			"Seven line verse", "Eight line verse", "Eight line verse / Four line chorus", "Eight line verse / Eight line chorus",
			"Nine line verse", "Ten line verse", "Twelve line verse", "Sixteen line verse", "Split chorus", "Woven",
			"Woven / Split chorus", "Irregular"),
		"place_of_origin"=>array(
			"Scotland", "Nova Scotia", "Prince Edward Island", "Ontario", "United States", "Other", "Unknown"
		),
		"gender_voice"=>array(
			"Male", "Female", "Male-Female", "Unknown", "Not Applicable"
		),
		"composer_gender"=>array(
			"Male", "Female", "Other", "Unknown"
		),
		"general_material_description"=>array(
			"Sound Recording", "Manuscript", "Publication"
		)
	);
	//an array of database fields and the form type required for each field
	private $_formTypes = array(
		"classifications"=>"multiple",
		"structure"=>"select",
		"place_of_origin"=>"select",
		"gender_voice"=>"select",
		"composer_gender"=>"select",
		"general_material_description"=>"select",
		"notes_1"=>"text",
		"notes_2"=>"text",
		"notes_3"=>"text",
		"notes_4"=>"text"
	);

	public function __construct($record, $origin = "") {
		 $this->_record = $record;
		 $this->_origin = $origin;
	}

	public function show() {
		$id = $this->_record->getAI();
		$html = '<div class="container py-5">';
		if ($this->_origin) {
			$origin = models\functions::decodeOrigin($this->_origin);
			$html .= <<<HTML
				<div><a href="index.php?{$origin}" title="back"><<< back</a></div>
HTML;
		}
		$html .= <<<HTML
			<table class="table">
				<tbody>
HTML;
		$this->_record->load();   //loads the info from the database into the record
		foreach ($this->_record->getAllProps() as $name => $value) {
			$name = models\functions::getFriendlyName($name);
			if (mb_substr($value, 0, 4) == "http") {
				$value = models\functions::addAnchorTags($value, $value,"link", "_blank");
			}
			$html .= <<<HTML
				<tr>
					<td>{$name}</td>
					<td>{$value}</td>
				</tr>
HTML;
		}
        $closeButtonUrl = $_SESSION["loggedIn"] ? "index.php?m=admin" : "index.php?m=records&a=list";
		$html .= <<<HTML
			</tbody></table>
			<a class="btn btn-secondary" href="{$closeButtonUrl}" title="close">close</a>
			<!--a class="btn btn-primary" href="index.php?m=record&a=edit&id={$id}&o={$this->_origin}" title="Edit {$id}">edit</a-->
HTML;
        $html .= '</div>';  //end container div
		echo $html;
	}

	public function edit() {
		//check for admin status
		if (!$_SESSION["loggedIn"]) {
			echo <<<HTML
			<h2>Not authorised</h2>
HTML;
			return;
		}

		$displayOnlyFields = array("ai");
		$hiddenFields = <<<HTML
			<input type="hidden" name="ai" value="{$this->_record->getAI()}">
HTML;
		if ($_GET["id"] == -1) {    //if we are creating a new record
			$displayOnlyFields = array();
			$hiddenFields = "";
		}

		($_GET["id"] == -1) ? [] : array("ai"); // /set AI as non-editable for existing record
		$html = "";
		if ($this->_origin) {
			$html .= <<<HTML
				<div><a href="index.php?{$this->_origin}" title="back"><<< back</a></div>
HTML;
		}
		$html .= <<<HTML
            <div class='container py-5'>
            <div class='row'>
            <div class='col-12'>
			<form action="index.php?m=record&a=save&id={$this->_record->getAI()}" class='edit-record-form' method="post">
HTML;
		$this->_record->load();   //loads the info from the database into the record
		foreach ($this->_record->getAllProps() as $name => $value) {
            $friendlyName = models\functions::getFriendlyName($name);

            if($name == 'ai' && $value != '') {
                $html .= <<<HTML
                    <div class="col-12 mb-3"><h2 class='page-title'>Edit Record</h2></div>
HTML;
            } elseif($name == 'ai' && $value == '') {
                $html .= <<<HTML
                    <div class="col-12 mb-3"><h2 class='page-title'>Add A Record</h2></div>
HTML;
            }

			if (in_array($name, $displayOnlyFields)) {  //no form field, just display the data
				$formFieldHtml = '<p class="read-only">'.$value.'</p>';
			} else if (isset($this->_formTypes[$name])) {   //test if this field requires a specific form element
				$formFieldHtml = $this->_getFormFieldHtml($name, $value);
			} else {
				//set default form element : an input text field
				$value = htmlentities($value);  //deal with e.g. double quotes in field data
				$formFieldHtml = <<<HTML
					<input class="form-control" type="text" id="{$name}" name="{$name}" placeholder="{$friendlyName}" value="{$value}" class="form-control">
HTML;
			}

			$html .= <<<HTML
				<div class="form-group">
					<label for="{$name}" class="form-label">{$friendlyName}</label>
					{$formFieldHtml}
				</div>
HTML;
		}
		$html .= <<<HTML
					{$hiddenFields}
					<a class="btn btn-secondary" href="index.php" title="close">Cancel</a>
					<button type="submit" class="btn btn-primary">Save</button>
				</form>
                </div>
                </div>
                </div>
HTML;
		echo $html;
		$this->_writeJavascript();
	}

	/**
	 * Generates the HTML code required for a particular form element
	 * @param string $name : the name of the database field
	 * @param string $value : the value of the database field
	 * @return string : the HTML for the form element
	 */
	private function _getFormFieldHtml($name, $value) {
		switch ($this->_formTypes[$name]) {   //switch based on form element type for given DB field name
			case "text":
				$formFieldHtml = <<<HTML
							<textarea class="form-control" id="{$name}" name="{$name}" rows="4">{$value}</textarea>
HTML;
				break;
			case "select":
				$formFieldHtml = <<<HTML
							<select name="{$name}" id="{$name}" class="form-select">
								<option value="">--- select ---</option>
HTML;
				foreach ($this->_controlOptions[$name] as $option) {
					$selected = ($option == $value) ? "selected" : "";
					$formFieldHtml .= <<<HTML
								<option value="{$option}" $selected>{$option}</option>
HTML;
				}
				$formFieldHtml .= "</select>";
				break;
			case "multiple":
				$formFieldHtml = <<<HTML
							<select name="{$name}[]" id="{$name}" multiple class="form-select">
HTML;
				$selectedOptions = explode(" , ", $value);
				foreach ($this->_controlOptions[$name] as $option) {
					$selected = in_array($option, $selectedOptions) ? "selected" : "";
					$formFieldHtml .= <<<HTML
								<option value="{$option}" $selected>{$option}</option>
HTML;
				}
				$formFieldHtml .= "</select>";
                $formFieldHtml .= "<div class='form-text'>Ctrl/Command click to select multiple.</div>";
				break;
		}
		return $formFieldHtml;
	}

	private function _writeJavascript() {
		echo <<<HTML
			<script>
				$(function() {
				  $('#ai').change(function () {
				    $.getJSON('ajax.php?action=getRecordExists&ai=' + $(this).val(), function (data) {
				      if (data.exists == true) {
				        alert("That identifier is already in use");
				        $('#ai').val('').focus();
				      }
				    });
				  });
				});
			</script>
HTML;
	}
}