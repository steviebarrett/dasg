<?php

namespace views;

use models;

class records
{
    private $_model; // an instance of models\records

    public function __construct($model)
    {
        $this->_model = $model;
    }

    public function show()
    {
        $this->_writeIntro();
        $this->_writeRecordsTable();
        $this->_writeBrowseJavascript();
        $this->_writeModal();
    }

    public function showSearchForm()
    {
        $searchTermHtml = <<<HTML
        <thead>
            <tr>
                <th style="width: 15%">Relationship</th>
                <th style="width: 30%">Field(s)</th>
                <th style="width: 40%">Search</th>
                <th style="width: 15%">Add Condition</th>
            </tr>
        </thead>
HTML;

        $data = $_SESSION["searchForm"] ?? null;

        if (empty($data)) {
            $searchTermHtml .= <<<HTML
                <tr>
                    <td style="text-align: center;vertical-align: middle;">---</td>
                    <td>
                        <div class="form-group">
                            {$this->_getSearchFieldsDropdown()}
                        </div>
                    </td>
                    <td id="cell_0">
                        <input type="text" class="form-control" placeholder="Search" aria-label="Search" id="s0" name="s[0]" required autofocus>
                    </td>
                    <td><button type="button" class="btn btn-primary addRow"><i class="fas fa-plus"></i></button></td>
                </tr>
HTML;
        }

        $searchFieldCount = 1;
        if (!empty($data["s"]) && is_array($data["s"])) {
            $searchFieldCount = count($data["s"]);
        }

        if (!empty($data)) {
            $firstCol = "&nbsp;";
            for ($i = 0; $i < $searchFieldCount; $i++) {
                $autofocus = ($i === 0) ? "autofocus" : "";
                $operators = ["AND", "OR", "NOT"];

                if ($i > 0) {
                    $booleans = "";
                    foreach ($operators as $operator) {
                        $selected = (!empty($data["b"][$i]) && $data["b"][$i] === $operator) ? 'selected' : '';
                        $opEsc = models\functions::e($operator);
                        $booleans .= '<option value="' . $opEsc . '" ' . $selected . '>' . $opEsc . '</option>';
                    }
                    $firstCol = <<<HTML
                        <select name="b[{$i}]" class="form-control">
                            {$booleans}
                        </select>
HTML;
                }

                $plus = (!empty($data["s"]) && $i === (count($data["s"]) - 1))
                    ? '<button type="button" class="btn btn-success addRow"><i class="fas fa-plus"></i></button>'
                    : "";

                $searchField = "";
                $sf = (string)($data["searchField"][$i] ?? '');
                $opts = $sf !== '' ? models\records::getControlledVocabularies($sf) : null;

                if ($opts) {
                    $searchField = <<<HTML
                        <select class="form-control" name="s[{$i}]" required>
                            <option value="">--- select ---</option>
HTML;
                    foreach ($opts as $option) {
                        $searchSelected = ((string)($data["s"][$i] ?? '') === (string)$option) ? "selected" : "";
                        $optEsc = models\functions::e((string)$option);
                        $searchField .= <<<HTML
                            <option value="{$optEsc}" {$searchSelected}>{$optEsc}</option>
HTML;
                    }
                    $searchField .= "</select>";
                } else {
                    $valEsc = models\functions::e((string)($data["s"][$i] ?? ''));
                    $searchField = <<<HTML
                        <input type="text" placeholder="Search" aria-label="Search"
                            class="form-control" id="s{$i}" name="s[{$i}]"
                            value="{$valEsc}" {$autofocus}>
HTML;
                }

                $sfValue = (string)($data["searchField"][$i] ?? '');
                $searchTermHtml .= <<<HTML
                    <tr>
                        <td>{$firstCol}</td>
                        <td>
                            {$this->_getSearchFieldsDropdown($i, $sfValue)}
                        </td>
                        <td id="cell_{$i}">{$searchField}</td>
                        <td>{$plus}</td>
                    </tr>
HTML;
            }
        }

        $searchFieldCountEsc = models\functions::e((string)$searchFieldCount);

        $html = <<<HTML
            <div id="searchFormContainer">
            <div class='container py-5'>
                <div class='row'>
                    <div class='col-12'>
                        <h2 class='page-title'>Search Index</h2>
                    </div>
                    <div class='col-12 mt-3'>

                        <form id="searchForm" action="index.php" method="get">
                            <table id="searchElements" class="table bootstrap-table">
                                {$searchTermHtml}
                            </table>
                            {$this->_getSearchOptionsHtml($data)}
                            <div class="row mt-4">
                                <input type="hidden" name="m" value="records">
                                <input type="hidden" name="a" value="search">
                                <div class="col-12 buttons">
                                    <button type="button" id="resetForm" class="btn btn-secondary float-end mx-1">Reset</button>
                                    <button type="button" id="searchSubmit" class="btn btn-primary float-end mx-1">Search</button>
                                </div>

                            </div>
                            <input type="hidden" name="searchFieldCount" id="searchFieldCount" value="{$searchFieldCountEsc}">
                        </form>
                    </div>
                </div>
            </div>
            </div>
HTML;

        echo $html;
        $this->_writeSearchJavascript();
    }

    private function _writeModal()
    {
        $editHtml = "";
        if (!empty($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] === true) {
            $editHtml = <<<HTML
                <button type="button" data-id="" class="deleteRecord btn btn-danger">Delete</button>
                <button type="button" data-id="" class="editRecord btn btn-primary">Edit</button>
HTML;
        }

        echo <<<HTML
            <!-- Modal -->
            <div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-labelledby="recordModal" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    {$editHtml}
                  </div>
                </div>
              </div>
            </div>
HTML;
    }

    private function _writeIntro()
    {
        echo <<<HTML
            <div class='container-fluid py-5'>
                <div class='row'>
                    <div class='col-12'>
                        <h2 class='page-title'>Browse Index</h2>
HTML;
    }

    private function _getSearchOptionsHtml($data)
    {
        $regexSelected          = !empty($data['params'][3]) ? "checked" : "";
        $accinsSelected         = !empty($data['params'][0]) ? "checked" : "";
        $lenitionSelected       = !empty($data['params'][1]) ? "checked" : "";
        $exactSelected          = !empty($data['params'][2]) ? "checked" : "";
        $transcriptionsSelected = !empty($data['params'][4]) ? "checked" : "";
        $recordingsSelected     = !empty($data['params'][5]) ? "checked" : "";

        if (empty($_SESSION["searchForm"])) {
            $accinsSelected = $lenitionSelected = "checked";
        }

        $html = <<<HTML
            <div class="container">
                <div class="row">
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[3]" value="0">
                        <input type="checkbox" class="form-check-input" id="regex" name="params[3]" value="1" {$regexSelected}>
                        <label class="form-check-label" for="regex"><h5>Regular Expression</h5></label>
                    </div>
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[0]" value="0">
                        <input type="checkbox" class="form-check-input" id="accins" name="params[0]" value="1" {$accinsSelected}>
                        <label class="form-check-label" for="accins"><h5>Accent Insensitive</h5></label>
                    </div>
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[1]" value="0">
                        <input type="checkbox" class="form-check-input" id="lenins" name="params[1]" value="1" {$lenitionSelected}>
                        <label class="form-check-label" for="lenins"><h5>Lenition Insensitive</h5></label>
                    </div>
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[2]" value="0">
                        <input type="checkbox" class="form-check-input" id="exact" name="params[2]" value="1" {$exactSelected}>
                        <label class="form-check-label" for="exact"><h5>Exact word only (no substring)</h5></label>
                    </div>
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[4]" value="0">
                        <input type="checkbox" class="form-check-input" id="transcriptions" name="params[4]" value="1" {$transcriptionsSelected}>
                        <label class="form-check-label" for="transcriptions"><i class="fa-solid fa-file" style="color:#0d6efd" aria-hidden="true"></i> Records with transcription</label>
                    </div>
                    <div class="col-md-3 form-check">
                        <input type="hidden" name="params[5]" value="0">
                        <input type="checkbox" class="form-check-input" id="recordings" name="params[5]" value="1" {$recordingsSelected}>
                        <label class="form-check-label" for="recordings"><i class="fa-solid fa-headphones" style="color:#0d6efd" aria-hidden="true"></i> Records with recording</label>
                    </div>
                </div>
            </div>
HTML;

        return $html;
    }

    private function _getSearchFieldsDropdown($index = 0, $value = "")
    {
        $priorityFields = ["title", "composer_last_name", "classifications", "subjects", "place_of_origin"];
        $priorityListHtml = "";

        foreach ($priorityFields as $field) {
            $selected = ($value === $field) ? "selected" : "";
            $friendlyName = models\functions::getFriendlyName($field);

            $fieldEsc = models\functions::e($field);
            $friendlyEsc = models\functions::e((string)$friendlyName);

            $priorityListHtml .= <<<HTML
                <option value="{$fieldEsc}" {$selected}>{$friendlyEsc}</option>
HTML;
        }

        $idxEsc = models\functions::e((string)$index);
        $html = <<<HTML
                <select name="searchField[{$idxEsc}]" data-index="{$idxEsc}" class="form-control searchFieldSelect">
                    <option value="all">Search All Fields  ▿</option>
HTML;

        $html .= $priorityListHtml;

        $searchFields = $this->_model->getSearchQueryFields();
        foreach ($searchFields as $searchField) {
            if (in_array($searchField, $priorityFields, true)) {
                continue;
            }
            $selected = ($value === $searchField) ? "selected" : "";
            $friendlyName = models\functions::getFriendlyName($searchField);

            $sfEsc = models\functions::e((string)$searchField);
            $fnEsc = models\functions::e((string)$friendlyName);

            $html .= <<<HTML
                <option value="{$sfEsc}" {$selected}>{$fnEsc}</option>
HTML;
        }

        $html .= "</select>";
        return $html;
    }

    private function _writeRecordsTable()
    {
        $allRecords = $this->_model->getAllRecords();
        $allRecordsHtml = "";

        foreach ($allRecords as $record) {
            $ai = (string)($record["ai"] ?? '');
            $aiEsc = models\functions::e($ai);
            $aiUrl = models\functions::e(models\functions::urlEncode($ai));
            $allRecordsHtml .= '<li><a href="/LIL/index.php?m=record&a=view&ai=' . $aiUrl . '">' . $aiEsc . '</a></li>';
        }

        $gaelicFieldMap = models\record::getGaelicFieldMap();
        $backLinkUrl = "index.php?m=records&a=search&reload=true";
        $isBrowse = (($_GET["a"] ?? '') === "list");
        $displayFields = $this->_model->getSearchFields();

        if ($isBrowse) {
            $displayFields = $this->_model->getBrowseFields();
            $backLinkUrl = "index.php";
        }

        $headerHtml = "";
        foreach ($displayFields as $field) {
            $field = (string)$field;
            $friendlyName = ($field === "ai")
                ? "Identifier Number"
                : models\functions::getFriendlyName($field);

            $fieldEsc = models\functions::e($field);
            $friendlyEsc = models\functions::e((string)$friendlyName);
            $gaelicEsc = models\functions::e((string)($gaelicFieldMap[$field] ?? ''));

            $headerHtml .= <<<HTML
                <th data-field="{$fieldEsc}" data-sortable="true" data-search-highlight-formatter="customSearchFormatter">
                    <em>{$gaelicEsc}</em><br>{$friendlyEsc}
                </th>
HTML;
        }

        $searchInfo = $isBrowse ? "" : $this->_getSearchResultsHeader();
        $ajaxAction = $isBrowse ? "browseRecords" : "searchRecords";

        $searchStrings = "";
        $searchFields = "";
        $booleans = "";
        $params = "";

        if (!$isBrowse) {
            $searchStringsArr = [];
            $searchFieldsArr = [];
            $booleansArr = [];
            $paramsArr = [];

            foreach ((array)($_GET["s"] ?? []) as $i => $s) {
                $s = (string)$s;
                if ($s === 'mactalla') {
                    $s = 'mac-talla';
                }
                if (!empty($_GET["params"][1])) {
                    $s = models\functions::getLenited($s);
                }
                if (!empty($_GET["params"][0])) {
                    $s = models\functions::getAccentInsensitive($s);
                }
                $searchStringsArr[$i] = $s;
            }

            foreach ((array)($_GET["searchField"] ?? []) as $i => $s) {
                $searchFieldsArr[$i] = (string)$s;
            }

            foreach ((array)($_GET["b"] ?? []) as $i => $b) {
                $booleansArr[$i] = (string)$b;
            }

            for ($i = 0; $i < 6; $i++) {
                $paramsArr[$i] = (string)($_GET["params"][$i] ?? '0');
            }

            $searchStrings = addslashes(implode('|', $searchStringsArr));
            $searchFields  = implode('|', $searchFieldsArr);
            $booleans      = implode('|', $booleansArr);
            $booleans      = '|' . $booleans; // preserve existing indexing behaviour
            $params        = implode('|', $paramsArr);
        }

        // Safely embed values into JS
        $jsSearchStrings = json_encode((string)$searchStrings, JSON_UNESCAPED_UNICODE);
        $jsSearchFields  = json_encode((string)$searchFields, JSON_UNESCAPED_UNICODE);
        $jsBooleans      = json_encode((string)$booleans, JSON_UNESCAPED_UNICODE);
        $jsParams        = json_encode((string)$params, JSON_UNESCAPED_UNICODE);
        $jsAjaxAction    = json_encode((string)$ajaxAction, JSON_UNESCAPED_UNICODE);

        $html = <<<HTML
            {$searchInfo}
      <table id="table" data-toggle="table"
      class='table '
        data-ajax="ajaxRequest"
        data-search="true"
        data-side-pagination="server"
        data-search-highlight="true"
        data-pagination-v-align="both"
        data-pagination="true">
        <thead>
              <tr>
                  {$headerHtml}
              </tr>
          </thead>
      </table>

            <noscript>
                <ul>
                    {$allRecordsHtml}
                </ul>
            </noscript>

            <script>
              function ajaxRequest(params) {
                const searchStrings = {$jsSearchStrings};
                const searchFields  = {$jsSearchFields};
                const booleans      = {$jsBooleans};
                const paramsStr     = {$jsParams};
                const ajaxAction    = {$jsAjaxAction};

                let searchTerms = (searchStrings || '').split('|');

                params.data['searchStrings'] = searchStrings;
                params.data['searchFields']  = searchFields;
                params.data['booleans']      = booleans;
                params.data['params']        = paramsStr;

                $.getJSON('ajax.php?action=' + encodeURIComponent(ajaxAction) + '&' + $.param(params.data), {format: 'json'})
                  .then(function (res) {
                    $.each(res.rows, function (i, v) {
                      let ai = v["ai"];
                      let link = '<a href="#" class="recordLink" data-toggle="modal" data-target="#recordModal" data-id="'+ai+'">'+ai+'</a>';

                      if (v['text'] != null) {
                        link += '&nbsp;<a title="transcription" target="_blank" href="transcription.php?ai='+ai+'"><i class="fa-solid fa-file"></i></a>';
                      }

                      if (v['original_format'] == 'Sound Recording' && v['online_access'] && v['online_access'].substring(0, 4) == 'http') {
                        link += '&nbsp;&nbsp;<a title="sound recording" target="_blank" href="'+v['online_access']+'"><i class="fa-solid fa-headphones"></i></a>';
                      }

                      res.rows[i]["ai"] = link;

                      if (searchTerms[0] != '') {
                        $.each(v, function(j, value) {
                          if (j == 'ai' || j == 'text') {
                            return;
                          }
                          $.each(searchTerms, function(k, searchTerm) {
                            if (searchTerm == null) { return; }

                            searchTerm = searchTerm.replace('[[:<:]]', String.raw`\\b`);
                            searchTerm = searchTerm.replace('[[:>:]]', String.raw`\\b`);
                            searchTerm = searchTerm.replace('[[:alpha:]]', '[a-z]');
                            searchTerm = searchTerm.replace('[[:digit:]]', '[0-9]');
                            searchTerm = searchTerm.replace('[[:space:]]', String.raw`\\s`);

                            re = new RegExp(searchTerm, 'giu');
                            if (res.rows[i][j]) {
                              res.rows[i][j] = res.rows[i][j].replace(re, '<span class="highlight">$&</span>');
                            }
                          });
                        });
                      }
                    });
                    params.success(res)
                  });
              }
            </script>
HTML;

        $html .= '</div></div></div>';
        echo $html;
    }

    private function _getSearchResultsHeader()
    {
        $searchFields = $this->_model->getFriendlySearchQueryFields();

        $searchFieldsHtml = empty($_GET["searchField"])
            ? implode(", ", $searchFields)
            : models\functions::getFriendlyName(implode(", ", (array)($_GET["searchField"] ?? [])));

        $searchFieldsHtml = models\functions::getFriendlyName($searchFieldsHtml);

        $searchString = implode(', ', (array)($_GET["s"] ?? []));

        $searchStringEsc = models\functions::e((string)$searchString);
        $searchFieldsEsc = models\functions::e((string)$searchFieldsHtml);

        $html = <<<HTML
            <div id="resultsHeader">
                Showing results for <strong>{$searchStringEsc}</strong> in {$searchFieldsEsc}
HTML;
        return $html;
    }

    private function _writeBrowseJavascript()
    {
        echo <<<HTML
            <script>
                window.customSearchFormatter = function(value, searchText) {
                  if (value.toString().substring(0, 1) == '<') {return value.toString();}
                  return value.toString().replace(new RegExp('(' + searchText + ')', 'gim'), '<span class="highlight">$1</span>')
                }

                function escHtml(s){
                  return String(s).replace(/[&<>"']/g, function(m){
                    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
                  });
                }

                function escAttr(s){
                  return escHtml(s).replace(/`/g, '&#96;');
                }

                $(function () {
  var myModal = new bootstrap.Modal(document.getElementById('recordModal'));
  $('.search-input').prop('placeholder', 'Filter Table');

  $(document).on('click', '.recordLink', function () {
    let ai = $(this).attr('data-id');
    $('.editRecord').attr('data-id', ai);
    $('.deleteRecord').attr('data-id', ai);

    var html = '<dl>';

        $.getJSON('ajax.php?action=getRecord&ai=' + encodeURIComponent(ai), function (data) {
                      $.each(data, function (i, v) {
                        // v is expected to be like: { label_en, label_gd, value }
                        const en = (v && v.label_en != null) ? String(v.label_en) : '';
                        const gd = (v && v.label_gd != null) ? String(v.label_gd) : '';
                        const rawValue = (v && v.value != null) ? String(v.value) : '';
                
                        // treat empty/null/"null" as absent
                        if (!rawValue || rawValue === 'null') return;
                
                        // Build the <dd> content safely
                        let ddHtml = '';
                        if (/^https?:\/\//i.test(rawValue)) {   // support for links in the record
                          // href attribute must be escaped separately
                          ddHtml =
                            '<a href="' + escAttr(rawValue) + '" target="_blank" rel="noopener noreferrer">' +
                              escHtml(rawValue) +
                            '</a>';
                        } else {
                          ddHtml = escHtml(rawValue);
                        }
                
                        html += '<dt><span class="text-muted"><em>' + escHtml(gd) + '</em></span><br>' + escHtml(en) + '</dt>';
                        html += '<dd>' + ddHtml + '</dd>';
                      });
                    })
                    .done(function () {
                      html += '</dl>';
                      $('.modal-title').text(ai);
                      $('.modal-body').html(html);
                      myModal.show();
                    })
                    .fail(function (xhr) {
                      console.error('getRecord failed', xhr.status, xhr.responseText);
                      alert('Failed to load record (' + xhr.status + ').');
                    });
                  });


                  $(document).on('click', '.editRecord', function () {
                    let ai = $(this).attr('data-id');

                    $.ajax({
                      url: 'ajax.php?action=editRecord',
                      method: 'POST',
                      headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
                      dataType: 'html',
                      data: { ai: ai }
                    })
                    .done(function (data) {
                      $('#recordModal').removeClass('fade');
                      $('#recordModal').modal('hide');
                      $('#mainBody').html(data);
                    });
                  });

                  $(document).on('click', '.deleteRecord', function () {
                    let ai = $(this).attr('data-id');
                    if (!confirm('Are you sure you want to delete record '+ai)) {
                      return;
                    }
                    $.ajax({
                      url: 'ajax.php?action=deleteRecord&ai=' + encodeURIComponent(ai)
                    })
                    .done(function () {
                      alert('Record deleted');
                      location.reload();
                    });
                  });

                });
            </script>
HTML;
    }

    private function _writeSearchJavascript()
    {
        echo <<<HTML
            <script>
                $(function () {

                  $(document).on('change', '.searchFieldSelect', function () {
                    let field = $(this).val();
                    let index = $(this).attr('data-index');
                    let inputVal = $('#s'+index).val();
                    if (typeof(inputVal) == "undefined") {
                      inputVal = "";
                    }
                    $.getJSON('ajax.php?action=getDropdownOptions&field='+encodeURIComponent(field), function (data) {
                      if (data == null) {
                        var html = '<input type="text" value="'+inputVal+'" id="s'+index+'" name="s['+index+']" class="form-control" placeholder="Search" aria-label="Search" required>';
                        $('#cell_'+index).html(html);
                        return;
                      }
                      var html = '<select name="s['+index+']" id="s'+index+'" data-index="'+index+'" class="form-control" required>';
                      html += '<option value="">--- select ---</option>';
                      $.each(data, function(i, value) {
                        html += '<option value="'+value+'">'+value+'</option>';
                      })
                      html += '</select>';
                      $('#cell_'+index).html(html);
                    })
                  });

                  let regexIsChecked = $('#regex').attr("checked") == "checked";
                  setSearchParams(regexIsChecked);

                  var searchFieldCount = $('#searchFieldCount').val();
                  $(document).on('click', '.addRow', function () {
                    $('.addRow').hide();
                    var booleanDropdown = '<select class="form-select" name="b[' + searchFieldCount + ']">';
                    booleanDropdown += '<option value = "AND">AND</option>';
                    booleanDropdown += '<option value = "OR">OR</option>';
                    booleanDropdown += '<option value = "AND NOT">NOT</option>';
                    booleanDropdown += '</select>';

                    $.ajax({
                      url: 'ajax.php?action=getSearchFieldDropdown',
                      method: 'POST',
                      headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
                      dataType: 'html',
                      data: { searchFieldCount: searchFieldCount },
                      success: function(data) {
                        var html = '<tr><td>' + booleanDropdown + '</td>';
                        html += '<td>' + data + '</td>';
                        html += '<td id="cell_'+searchFieldCount+'"><input type="text" class="form-control" placeholder="Search"';
                        html += ' id="s'+searchFieldCount+'" aria-label="Search" name="s[' + searchFieldCount + ']" autofocus required></td>';
                        html += '<td><button type="button" class="btn btn-primary addRow"><i class="fas fa-plus"></i></button></td></tr>';
                        $('#searchElements').append(html);
                        searchFieldCount ++;
                      }
                    });
                  });

                  $('#regex').on('click', function() {
                    setSearchParams(this.checked);
                  });

                  $('form').on('submit', function (event) {
                    event.preventDefault();
                  });

                  $('#resetForm').on('click', function (e) {
                      e.preventDefault();

                      $.ajax({
                        url: 'ajax.php?action=resetSearchForm',
                        method: 'POST',
                        dataType: 'text',
                        headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
                        success: function (resp) {
                          window.location.reload();
                        },
                        error: function (xhr) {
                          console.error('resetSearchForm failed', xhr.status, xhr.responseText);
                          alert('Reset failed (' + xhr.status + '). Please refresh and try again.');
                        }
                      });
                    });

                  $('#searchSubmit').on('click', function () {
                    let form = $('#searchForm');
                    let url = 'ajax.php?action=saveSearchForm';
                    let data = form.serialize();
                    $.ajax({
                      url: url,
                      method: 'post',
                      data: data,
                      headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
                      success: function (response) {
                        const ok = (response === true || response === '1' || response === 1 || response === 'true');
                        if (ok) {
                          var validator = $( "#searchForm" ).validate();
                          if(validator.form()) {
                            form[0].submit();
                          }
                        }
                      }
                    });
                  });
                });

                function setSearchParams(regexIsChecked) {
                  if(regexIsChecked) {
                        $('#accins').attr("disabled", true);
                        $('#accins').attr("checked", false);
                        $('#lenins').attr("disabled", true);
                        $('#lenins').attr("checked", false);
                        $('#exact').attr("disabled", true);
                        $('#exact').attr("checked", false);
                    } else {
                      $('#accins').attr("disabled", false);
                        $('#lenins').attr("disabled", false);
                        $('#exact').attr("disabled", false);
                        $('#exact').attr("checked", false);
                    }
                }
            </script>
HTML;
    }
}