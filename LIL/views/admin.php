<?php


namespace views;

use models;

class admin
{
	private $_model;  //an instance of models\records
	private $_recordFieldNames; //array: the complete list of possible field names for a record

	public function __construct($model) {
		$this->_model = $model;
		$this->_recordFieldNames = $this->_model->getAllFieldNames();
	}

	public function show() {
		$html = <<<HTML
			<div class='page-content'>
            <div class='container py-5'>
                <div class="row">
                    <div class="col-12">
                        <h2 class='page-title'>Admin Dashboard</h2>
                    </div>
                    <div class="adminSection col-md-4 mb-4">
                        <div class="card expandable">
                                {$this->_getSearchQueryOptionsHtml()}
                        </div>
                    </div>
                    <div class="adminSection col-md-4 mb-4">
                        <div class="card expandable">
                                {$this->_getSearchOptionsHtml()}
                        </div>
                    </div>
                    <div class="adminSection col-md-4 mb-4">
                        <div class="card expandable">
                                {$this->_getBrowseOptionsHtml()}
                        </div>
                    </div>
                    <div class="adminSection col-md-6 mb-4">
                        <div class="card">
                                {$this->_getRecordOptionsHtml()}
                        </div>
                    </div>
                    <div class="adminSection col-md-6 mb-4">
                        <div class="card">
                                {$this->_getExportToExcelHtml()}
                        </div>
                    </div>
                </div>
            </div>
			</div>
HTML;
		echo $html;
		$this->_writeJavascript();
	}

	public function writeLoginForm() {
		echo <<<HTML
			<h2 class="page-title">Admin Login</h2>
			<form name="login" method="post" action="index.php?m=admin&a=login">
				<div>
			    <label for="u">username:</label>
			    <input type="username" id="u" name="u" placeholder="username">
			  </div>
			  <div>
			    <label for="p">password:</label>
			    <input type="password" id="p" name="p" placeholder="password">
			  </div>
			  <button type="submit">Submit</button>
			</form>
HTML;
	}

	private function _getSearchQueryOptionsHtml() {
		$html = <<<HTML
            <div class='card-body'>
			<h3 class="text-muted">Search Query Fields</h3>
			<p><note>These fields will be searched when a new query is submitted</note></p>
            </div>
            <div class="card-footer">
			<a id="searchQueryFieldsLink" href="#" class='btn btn-primary'>Show</a>
			<div id="searchQueryFields">
HTML;

		$selectedFields = $this->_model->getSearchQueryFields();
		$html .= '<ul class="admin-list">';
		foreach ($this->_recordFieldNames as $fieldName) {
			$checked = in_array($fieldName, $selectedFields) ? "checked" : "";
			$friendlyName = models\functions::getFriendlyName($fieldName);
			$html .= <<<HTML
				<li>
					<input type="checkbox" class="searchQueryOption" id="{$fieldName}" name="{$fieldName}" {$checked}>

					<label for="{$fieldName}">{$friendlyName}</label>
				</li>
HTML;
		}
		$html .= "</ul></div></div>";
		return $html;
	}

	private function _getSearchOptionsHtml() {
		$html = <<<HTML
            <div class='card-body'>
			<h3 class="text-muted">Search Display Fields</h3>
			<p><note>These fields will be shown in the search results table</note></p>
            </div>
            <div class="card-footer">
			<a id="searchFieldsLink" href="#" class='btn btn-primary'>Show</a>
			<div id="searchFields" class='pt-3'>
				<p><note><em>AI is required for search display so is not listed here.</em></note></p>
HTML;

		$selectedFields = $this->_model->getSearchFields();
		$html .= '<ul class="admin-list pt-0">';
		foreach ($this->_recordFieldNames as $fieldName) {
			if ($fieldName == "ai") {
				continue;
			}
			$checked = in_array($fieldName, $selectedFields) ? "checked" : "";
			$friendlyName = models\functions::getFriendlyName($fieldName);
			$html .= <<<HTML
				<li>
					<input type="checkbox" class="searchOption" id="{$fieldName}" name="{$fieldName}" {$checked}>
					<label for="{$fieldName}">{$friendlyName}</label>
				</li>
HTML;
		}
		$html .= "</ul></div></div>";
		return $html;
	}

	private function _getBrowseOptionsHtml() {
		$html = <<<HTML
            <div class='card-body'>
			<h3 class="text-muted">Browse Display Fields</h3>
			<p><note>These fields will be shown in the browse table</note></p>
            </div>
            <div class="card-footer">
			<a id="browseFieldsLink" href="#" class='btn btn-primary'>Show</a>
			<div id="browseFields" class='pt-3'>
				<p><note><em>AI is required for browse display so is not listed here.</em></note></p>
HTML;

		$selectedFields = $this->_model->getBrowseFields();
		$html .= '<ul class="admin-list pt-0">';
		foreach ($this->_recordFieldNames as $fieldName) {
			if ($fieldName == "ai") {
				continue;
			}
			$checked = in_array($fieldName, $selectedFields) ? "checked" : "";
			$friendlyName = models\functions::getFriendlyName($fieldName);
			$html .= <<<HTML
				<li>
					<input type="checkbox" class="browseOption" id="{$fieldName}" name="{$fieldName}" {$checked}>

					<label for="{$fieldName}">{$friendlyName}</label>
				</li>
HTML;
		}
		$html .= "</ul></div></div>";
		return $html;
	}

	private function _getRecordOptionsHtml() {
		$html = <<<HTML
            <div class='card-body'>
                <h3 class="text-muted">Record Options</h3>
                <p><note>Add a new record into the table.</note></p>
            </div>
            <div class="card-footer">
                <a href="index.php?m=record&a=edit&id=-1" target="_blank" title="Add a record" class='btn btn-primary'>Add A Record</a>
            </div>
HTML;
		return $html;
	}

	private function _getExportToExcelHtml() {
		$html = <<<HTML
            <div class='card-body'>
			    <h3 class="text-muted">Export Options</h3>
                <p><note>Export the data into a CSV.</note></p>
            </div>
            <div class="card-footer">
                <a href="export.php" target="_blank" title="export to excel" class='btn btn-primary'>Export to Excel</a>
            </div>
HTML;
		return $html;
	}

	private function _writeJavascript() {
		echo <<<HTML
			<script>
				$(function() {
				  $('.searchQueryOption').on('click', function () {
				    $.ajax('ajax.php?action=updateSearchQueryOptions&field='+$(this).attr("id")+'&checked='+$(this).prop("checked"));
				  });

				  $('.searchOption').on('click', function () {
				    $.ajax('ajax.php?action=updateSearchOptions&field='+$(this).attr("id")+'&checked='+$(this).prop("checked"));
				  });

				  $('.browseOption').on('click', function () {
				    $.ajax('ajax.php?action=updateBrowseOptions&field='+$(this).attr("id")+'&checked='+$(this).prop("checked"));
				  });

				  $('#browseFieldsLink').on('click', function() {
				    if ($('#browseFieldsLink').text() == "Show") {
				      $('#browseFields').show();
				      $('#browseFieldsLink').text("Hide");
				    } else {
				      $('#browseFields').hide();
				      $('#browseFieldsLink').text("Show");
				    }
				  });

				  $('#searchFieldsLink').on('click', function() {
				    if ($('#searchFieldsLink').text() == "Show") {
				      $('#searchFields').show();
				      $('#searchFieldsLink').text("Hide");
				    } else {
				      $('#searchFields').hide();
				      $('#searchFieldsLink').text("Show");
				    }
				  });

				  $('#searchQueryFieldsLink').on('click', function() {
				    if ($('#searchQueryFieldsLink').text() == "Show") {
				      $('#searchQueryFields').show();
				      $('#searchQueryFieldsLink').text("Hide");
				    } else {
				      $('#searchQueryFields').hide();
				      $('#searchQueryFieldsLink').text("Show");
				    }
				  });
				});
			</script>
HTML;

	}
}