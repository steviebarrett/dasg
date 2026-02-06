<?php

declare(strict_types=1);

namespace views;

use models\functions;
use models\records;

class admin
{
    private records $_model;                 // an instance of models\records
    private array $_recordFieldNames = [];   // complete list of possible field names for a record

    public function __construct($model)
    {
        /** @var records $model */
        $this->_model = $model;
        $this->_recordFieldNames = $this->_model->getAllFieldNames();
    }

    public function show(): void
    {
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

    public function writeLoginForm(): void
    {
        $token = functions::e((string)($_SESSION['csrf_token'] ?? ''));

        echo <<<HTML
			<h2 class="page-title">Admin Login</h2>
			<form name="login" method="post" action="index.php?m=admin&a=login" autocomplete="off">
				<div>
			        <label for="u">username:</label>
			        <input type="text" id="u" name="u" placeholder="username" autocomplete="username">
			    </div>
			    <div>
			        <label for="p">password:</label>
			        <input type="password" id="p" name="p" placeholder="password" autocomplete="current-password">
			    </div>
			    <input type="hidden" name="_csrf" value="{$token}">
			    <button type="submit">Submit</button>
			</form>
HTML;
    }

    private function _getSearchQueryOptionsHtml(): string
    {
        $html = <<<HTML
            <div class='card-body'>
			    <h3 class="text-muted">Search Query Fields</h3>
			    <p><note>These fields will be searched when a new query is submitted</note></p>
            </div>
            <div class="card-footer">
			    <a id="searchQueryFieldsLink" href="#" class='btn btn-primary'>Show</a>
			    <div id="searchQueryFields" style="display:none" class="pt-3">
HTML;

        $selectedFields = $this->_model->getSearchQueryFields();
        $html .= '<ul class="admin-list">';

        foreach ($this->_recordFieldNames as $fieldName) {
            $fieldName = (string)$fieldName;

            $checked = in_array($fieldName, $selectedFields, true) ? "checked" : "";
            $friendlyName = functions::e(functions::getFriendlyName($fieldName));

            // escape for attribute context
            $idEsc = functions::e($fieldName);

            $html .= <<<HTML
				<li>
					<input type="checkbox" class="searchQueryOption" id="{$idEsc}" name="{$idEsc}" {$checked}>
					<label for="{$idEsc}">{$friendlyName}</label>
				</li>
HTML;
        }

        $html .= "</ul></div></div>";
        return $html;
    }

    private function _getSearchOptionsHtml(): string
    {
        $html = <<<HTML
            <div class='card-body'>
			    <h3 class="text-muted">Search Display Fields</h3>
			    <p><note>These fields will be shown in the search results table</note></p>
            </div>
            <div class="card-footer">
			    <a id="searchFieldsLink" href="#" class='btn btn-primary'>Show</a>
			    <div id="searchFields" style="display:none" class='pt-3'>
				    <p><note><em>AI is required for search display so is not listed here.</em></note></p>
HTML;

        $selectedFields = $this->_model->getSearchFields();
        $html .= '<ul class="admin-list pt-0">';

        foreach ($this->_recordFieldNames as $fieldName) {
            $fieldName = (string)$fieldName;

            if ($fieldName === "ai") {
                continue;
            }

            $checked = in_array($fieldName, $selectedFields, true) ? "checked" : "";
            $friendlyName = functions::e(functions::getFriendlyName($fieldName));
            $idEsc = functions::e($fieldName);

            $html .= <<<HTML
				<li>
					<input type="checkbox" class="searchOption" id="{$idEsc}" name="{$idEsc}" {$checked}>
					<label for="{$idEsc}">{$friendlyName}</label>
				</li>
HTML;
        }

        $html .= "</ul></div></div>";
        return $html;
    }

    private function _getBrowseOptionsHtml(): string
    {
        $html = <<<HTML
            <div class='card-body'>
			    <h3 class="text-muted">Browse Display Fields</h3>
			    <p><note>These fields will be shown in the browse table</note></p>
            </div>
            <div class="card-footer">
			    <a id="browseFieldsLink" href="#" class='btn btn-primary'>Show</a>
			    <div id="browseFields" style="display:none" class='pt-3'>
				    <p><note><em>AI is required for browse display so is not listed here.</em></note></p>
HTML;

        $selectedFields = $this->_model->getBrowseFields();
        $html .= '<ul class="admin-list pt-0">';

        foreach ($this->_recordFieldNames as $fieldName) {
            $fieldName = (string)$fieldName;

            if ($fieldName === "ai") {
                continue;
            }

            $checked = in_array($fieldName, $selectedFields, true) ? "checked" : "";
            $friendlyName = functions::e(functions::getFriendlyName($fieldName));
            $idEsc = functions::e($fieldName);

            $html .= <<<HTML
				<li>
					<input type="checkbox" class="browseOption" id="{$idEsc}" name="{$idEsc}" {$checked}>
					<label for="{$idEsc}">{$friendlyName}</label>
				</li>
HTML;
        }

        $html .= "</ul></div></div>";
        return $html;
    }

    private function _getRecordOptionsHtml(): string
    {
        // harden: rel noopener on target=_blank
        $html = <<<HTML
            <div class='card-body'>
                <h3 class="text-muted">Record Options</h3>
                <p><note>Add a new record into the table.</note></p>
            </div>
            <div class="card-footer">
                <a href="index.php?m=record&a=edit&id=-1" target="_blank" rel="noopener noreferrer" title="Add a record" class='btn btn-primary'>Add A Record</a>
            </div>
HTML;
        return $html;
    }

    private function _getExportToExcelHtml(): string
    {
        // export.php moved out of the web director
        // Keeping the UI
        $html = <<<HTML
            <div class='card-body'>
			    <h3 class="text-muted">Export Options</h3>
                <p><note>Export the data into a CSV.</note></p>
            </div>
            <div class="card-footer">
                <span class="text-muted">Export script is not web-accessible.</span>
            </div>
HTML;
        return $html;
    }

    private function _writeJavascript(): void
    {
        echo <<<HTML
			<script>
				$(function() {

                  function postOption(action, field, checked) {
                    // checked must be string 'true'/'false' to match server expectations
                    const checkedStr = checked ? 'true' : 'false';

                    return $.ajax({
                      url: 'ajax.php?action=' + encodeURIComponent(action),
                      method: 'POST',
                      dataType: 'json',
                      data: { field: field, checked: checkedStr },
                      headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
                    }).fail(function(xhr) {
                      console.error(action + ' failed', xhr.status, xhr.responseText);
                      alert('Update failed (' + xhr.status + '). Please refresh and try again.');
                    });
                  }

				  $('.searchQueryOption').on('click', function () {
                    postOption('updateSearchQueryOptions', $(this).attr('id'), $(this).prop('checked'));
				  });

				  $('.searchOption').on('click', function () {
                    postOption('updateSearchOptions', $(this).attr('id'), $(this).prop('checked'));
				  });

				  $('.browseOption').on('click', function () {
                    postOption('updateBrowseOptions', $(this).attr('id'), $(this).prop('checked'));
				  });

                  function toggleBlock(linkSel, blockSel) {
                    const \$link = $(linkSel);
                    const \$block = $(blockSel);
                    if (\$link.text() === "Show") {
                      \$block.show();
                      \$link.text("Hide");
                    } else {
                      \$block.hide();
                      \$link.text("Show");
                    }
                  }

				  $('#browseFieldsLink').on('click', function(e) {
                    e.preventDefault();
                    toggleBlock('#browseFieldsLink', '#browseFields');
				  });

				  $('#searchFieldsLink').on('click', function(e) {
                    e.preventDefault();
                    toggleBlock('#searchFieldsLink', '#searchFields');
				  });

				  $('#searchQueryFieldsLink').on('click', function(e) {
                    e.preventDefault();
                    toggleBlock('#searchQueryFieldsLink', '#searchQueryFields');
				  });
				});
			</script>
HTML;
    }
}