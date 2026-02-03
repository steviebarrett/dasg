<?php


namespace views;
use models;

class records
{
	private $_model; //an instance of models\records

	public function __construct($model) {
		$this->_model = $model;
	}

	public function show() {
		$this->_writeIntro();
		$this->_writeRecordsTable();
		$this->_writeBrowseJavascript();
		$this->_writeModal();
	}

	public function showSearchForm() {
		//defaults to lenition and accent insensitve to on
		$searchTermHtml = <<<HTML
        <thead>
            <tr>
                <th style="width: 15%">
                    Relationship
                </th>
                <th style="width: 30%">
                    Field(s)
                </th>
                <th style="width: 40%">
                    Search
                </th>
                <th style="width: 15%">
                    Add Condition
                </th>
            </tr>
        </thead>
HTML;
		//if there is no saved form data then add defaults
		$data = $_SESSION["searchForm"];

		if (empty($data)) {
			$searchTermHtml .= <<<HTML
				<tr>
					<td style="text-align: center;vertical-align: middle;">---</td>
					<td>
						<div class="form-group>">
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

		//reload previous search form
        $searchFieldCount = 1;
        if ($data["s"]) {
            $searchFieldCount = count($data["s"]);
        }
		if ($data) {
			$firstCol = "&nbsp;";
			for ($i = 0; $i < $searchFieldCount; $i++) {
				$autofocus = $i == 0 ? "autofocus" : "";
				$operators = array("AND", "OR", "NOT");
				if ($i > 0) {
					$booleans = "";
					foreach ($operators as $operator) {
						$selected = $data["b"][$i] == $operator ? 'selected' : '';
						$booleans .= '<option value = "' . $operator . '" ' . $selected. '>' . $operator . '</option>';
					}
					$firstCol = <<<HTML
						<select name="b[{$i}]" class="form-control">
							{$booleans}
						</select>
HTML;
				}
				$plus = $i == (count($data["s"]) - 1) ? '<button type="button" class="btn btn-success addRow"><i class="fas fa-plus"></i></button>': "";
				//check for controlled vocabularies
				if ($options = models\records::getControlledVocabularies($data["searchField"][$i])) {
					$searchField = <<<HTML
						<select class="form-control" name="s[{$i}]" required>
							<option value="">--- select ---</option>
HTML;
					foreach ($options as $option) {
						$searchSelected = ($data["s"][$i] == $option) ? "selected" : "";
						$searchField .= <<<HTML
							<option value="{$option}" {$searchSelected}>{$option}</option>
HTML;
					}
					$searchField .= "</select>";
				} //end check for controlled vocabularies
				$searchField = <<<HTML
					<input type="text" placeholder="Search" aria-label="Search" class="form-control" id=s{$i}" name="s[{$i}]" value="{$data["s"][$i]}" {$autofocus}>
HTML;
				$searchTermHtml .= <<<HTML
					<tr>
						<td>{$firstCol}</td>
						<td>
								{$this->_getSearchFieldsDropdown($i, $data["searchField"][$i])}
						</td>
						<td id="cell_{$i}">{$searchField}</td>
						<td>{$plus}</td>
					</tr>
HTML;
			}
		}

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
                                    <button type="submit" id="searchSubmit" class="btn btn-primary float-end mx-1">Search</button>
                                </div>

                            </div>
                            <input type="hidden" name="searchFieldCount" id="searchFieldCount" value="{$searchFieldCount}">
                        </form>
                    </div>
                </div>
			</div>
			</div>
HTML;
		echo $html;
		$this->_writeSearchJavascript();;
	}

	private function _writeModal() {
		$editHtml = "";
		//check for admin status
		if ($_SESSION["loggedIn"]) {
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

	private function _writeIntro() {
		echo <<<HTML
			<!-- <div class='col-12'>
                <h2 class='page-title'>Language In Lyrics</h2>
            </div> -->
            <div class='container-fluid py-5'>
                <div class='row'>
                    <div class='col-12'>
                        <h2 class='page-title'>Browse Index</h2>
HTML;
	}

	private function _getSearchOptionsHtml($data) {

		$regexSelected = $data["params"][3] ? "checked" : "";
		$accinsSelected = $data["params"][0] ? "checked" : "";
		$lenitionSelected = $data["params"][1] ? "checked" : "";
		$exactSelected = $data["params"][2] ? "checked" : "";
        $transcriptionsSelected = $data["params"][4] ? "checked" : "";
        $recordingsSelected = $data["params"][5] ? "checked" : "";

        if (!$_SESSION["searchForm"]) {$accinsSelected = $lenitionSelected = "checked";}    //default to on

		$html = <<<HTML
			<div class="container">
				<div class="row">
					<!-- <div class="col-sm"></div> -->
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="regex" name="params[3]" $regexSelected>
						<label class="form-check-label" for="regex"><h5>Regular Expression</h5></label>
					</div>
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="accins" name="params[0]" $accinsSelected>
						<label class="form-check-label" for="accins"><h5>Accent Insensitive</h5></label>
					</div>
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="lenins" name="params[1]" $lenitionSelected>
						<label class="form-check-label" for="lenins"><h5>Lenition Insensitive</h5></label>
					</div>
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="exact" name="params[2]" $exactSelected>
						<label class="form-check-label" for="exact"><h5>Exact word only (no substring)</h5></label>
					</div>
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="transcriptions" name="params[4]" $transcriptionsSelected>
						<label class="form-check-label" for="transcriptions"><i class="fa-solid fa-file" style="color:#0d6efd" aria-hidden="true"></i> Records with transcription</label>
					</div>
					<div class="col-md-3 form-check">
						<input type="checkbox" class="form-check-input" id="recordings" name="params[5]" $recordingsSelected>
						<label class="form-check-label" for="recordings"><i class="fa-solid fa-headphones" style="color:#0d6efd" aria-hidden="true"></i> Records with recording</label>
					</div>
				</div>
			</div>
HTML;
		return $html;
	}

	private function _getRegexHelpHtml() {
		$html = <<<HTML
			<div id="regExpHelp" class="hide mt-3">
				<table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Expression</th>
                            <th scope="col">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>.</td>
                        <td>Any single character. "th.g" will match "thig" and "thug"</td>
                        </tr>
                        <tr>
                            <td>*</td>
                        <td>0 or more of the previous string. ".*as" will match e.g. "Aonghas", "Seumas", "Dileas"</td>
                        </tr>
                        <tr>
                            <td>?</td>
                        <td>0 or 1 of the previous. "bh?reacai?n " will match e.g. "bhreacan", "breachain"</td>
                        </tr>
                        <tr>
                            <td>+</td>
                        <td>1 or more of the previous. "chu.+" will match e.g. "chuir", "chulthaobh", "Chuala"</td>
                        </tr>
                        <tr>
                        <td>[xyz]</td>
                        <td>a range of characters. "bh[aio]" will match e.g. "bha", "bhi", "bho"
                        </tr>
                        <tr>
                        <td>[^xyz]</td>
                        <td>a range of characters other than those specified. "bh[^io]+" will match e.g. "bha" but NOT "bho" or "bhi"</td>
                        </tr>
                        <tr>
                        <td>[[:space:]]</td>
                        <td>whitespace. "bha[[:space:]]" will match e.g. "bha "
                        </tr>
                    </tbody>
				</table>
			</div>
HTML;
		return $html;
	}

	private function _getSearchFieldsDropdown($index = 0, $value = "") {
		//these fields are to be displayed at the top of the list
		$priorityFields = array("title", "composer_last_name", "classifications", "subjects", "place_of_origin");
		foreach ($priorityFields as $field) {
			$selected = $value == $field ? "selected" : "";
			$friendlyName = models\functions::getFriendlyName($field);
			$priorityListHtml .= <<<HTML
				<option value="{$field}" {$selected}>{$friendlyName}</option>
HTML;
		}
		$html = <<<HTML
				<select name="searchField[{$index}]" data-index="{$index}" class="form-control searchFieldSelect">
					<option value="all">Search All Fields  ▿</option>
HTML;
		$html .= $priorityListHtml;
		$searchFields = $this->_model->getSearchQueryFields();
		foreach ($searchFields as $searchField) {
			if (in_array($searchField, $priorityFields)) {  //skip any priority field
				continue;
			}
			$selected = $value == $searchField ? "selected" : "";
			$friendlyName = models\functions::getFriendlyName($searchField);
			$html .= <<<HTML
				<option value="{$searchField}" {$selected}>{$friendlyName}</option>
HTML;
		}
		$html .= "</select>";
		return $html;
	}

	private function _writeRecordsTable() {
        $allRecords = $this->_model->getAllRecords();
        $allRecordsHtml = "";
        foreach ($allRecords as $record) {
            $allRecordsHtml .= '<li><a href="/LIL/index.php?m=record&a=view&ai=' . $record["ai"] . '">' . $record["ai"] . '</a></li>';
        }
		$gaelicFieldMap = models\record::getGaelicFieldMap();
		$backLinkUrl = "index.php?m=records&a=search&reload=true";
		$isBrowse = ($_GET["a"] == "list") ? true : false;
		$displayFields = $this->_model->getSearchFields();
		if ($isBrowse) {
			$displayFields = $this->_model->getBrowseFields();
			$backLinkUrl = "index.php";
		}
		$headerHtml = "";
		foreach ($displayFields as $field) {
			$friendlyName = ($field == "ai")
				? "Identifier Number"
				: models\functions::getFriendlyName($field);
			$headerHtml .= <<<HTML
				<th data-field="{$field}" data-sortable="true" data-search-highlight-formatter="customSearchFormatter">
					<em>{$gaelicFieldMap[$field]}</em><br>{$friendlyName}
				</th>
HTML;
		}
		$searchInfo = $isBrowse ? "" : $this->_getSearchResultsHeader();
		$ajaxAction = $isBrowse ? "browseRecords" : "searchRecords";

		//process search data for passing via AJAX
		if (!$isBrowse) {
            $booleans = array();
			foreach($_GET["s"] as $i => $s) {
				if ($s == 'mactalla') {
					$s = 'mac-talla';       //hack to ensure searches for 'mactalla' also search for 'mac-talla'
				}
				if ($_GET["params"][1]) {
					$s = models\functions::getLenited($s);
				}
				if ($_GET["params"][0]) {
					$s = models\functions::getAccentInsensitive($s);
				}
//				$searchStrings[$i] = addslashes($s);
				$searchStrings[$i] = $s;

			}
			foreach($_GET["searchField"] as $i => $s) {
				$searchFields[$i] = $s;
			}
			foreach($_GET["b"] as $i => $b) {
				$booleans[$i] = $b;
			}
			for ($i=0; $i < 6; $i++) {
				$params[$i] = $_GET["params"][$i];
			}
			$searchStrings = addslashes(implode('|', $searchStrings));
			$searchFields = implode('|', $searchFields);
			$booleans = implode('|', $booleans);
			$booleans = '|' . $booleans;  //solve indexing from zero issue
			$params = implode('|', $params);
		}

		$html = <<<HTML
			<!-- <div><a href="{$backLinkUrl}" title="back" class='btn btn-primary'>Go Back</a></div> -->
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
            <!-- ensure all results are crawlable -->
            <noscript>
                <ul>
                    {$allRecordsHtml}
                </ul>
            </noscript>
            
			<script>
				/**
				* Runs an AJAX request to populate the Bootstrap table
				* @param params
				*/
			  function ajaxRequest(params) {
			    let searchTerms = '{$searchStrings}'.split('|');
			    params.data['searchStrings'] = '{$searchStrings}';
			    params.data['searchFields'] = '{$searchFields}';
			    params.data['booleans'] = '{$booleans}';
			    params.data['params'] = '{$params}';
			    $.getJSON( 'ajax.php?action={$ajaxAction}&' + $.param(params.data), {format: 'json'}).then(function (res) {
			      $.each(res.rows, function (i,v) {
			        //add the record links to the ai field
			        let ai = v["ai"];
			        let link = '<a href="#" class="recordLink" data-toggle="modal" data-target="#recordModal" data-id="'+ai+'">'+ai+'</a>';
			        //check for a transcription
			        if (v['text'] != null) {
			          link += '&nbsp;<a title="transcription" target="_blank" href="transcription.php?ai='+v["ai"]+'"><i class="fa-solid fa-file"></i></a>';
			        }
			        //check for a sound recording
			        if (v['original_format'] == 'Sound Recording' && v['online_access'].substring(0, 4) == 'http') {
			          link += '&nbsp;&nbsp;<a title="sound recording" target="_blank" href="'+v['online_access']+'"><i class="fa-solid fa-headphones"></i></a>';
			        }
			        res.rows[i]["ai"] = link;
			        //add the highlight span to the other fields
			        if (searchTerms[0] != '') {   //search, not browse
				        $.each(v, function(j, value) {
				          //skip the ai field
				          if (j == 'ai' || j == 'text') {  //don't parse `ai` or transcription `text` 
				            return;
				          } 
				          $.each(searchTerms, function(k, searchTerm) {
				            
				            if (searchTerm == null) { return; }
				            
				            //check for word boundaries
				            searchTerm = searchTerm.replace('[[:<:]]', String.raw`\b`);
				            searchTerm = searchTerm.replace('[[:>:]]', String.raw`\b`);
				            //replace character classes
				            searchTerm = searchTerm.replace('[[:alpha:]]', '[a-z]');
				            searchTerm = searchTerm.replace('[[:digit:]]', '[0-9]');
				            
				            searchTerm = searchTerm.replace('[[:space:]]', String.raw`\s`);
				            
				            /*
				            if (searchTerm.includes('[[')) {
				              return;       //do not parse character classes etc.
				            }*/
				            
				      //      searchTerm.replace(/\[.*\]/giu, '');   //remove any character classes, etc.            
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

	private function _getSearchResultsHeader() {
		$searchFields = $this->_model->getFriendlySearchQueryFields();
		$searchFieldsHtml = empty($_GET["searchField"])
			? implode(", ", $searchFields)          //simple search - use admin search fields
			: models\functions::getFriendlyName(implode(", ", $_GET["searchField"]));  //advanced search - use user search fields
		$searchFieldsHtml = models\functions::getFriendlyName($searchFieldsHtml);
		$searchString = implode(', ', $_GET["s"]);
		$html = <<<HTML
			<div id="resultsHeader">
				Showing results for <strong>{$searchString}</strong> in {$searchFieldsHtml}
HTML;
		return $html;
	}

	private function _writeBrowseJavascript() {
		echo <<<HTML
			<script>
				//function to add fancy styling to filter results in Bootstrap Table
				window.customSearchFormatter = function(value, searchText) {
				  if (value.toString().substring(0, 1) == '<') {return value.toString();} //don't markup record links
          return value.toString().replace(new RegExp('(' + searchText + ')', 'gim'), '<span class="highlight">$1</span>')
        }

				$(function () {
				  var myModal = new bootstrap.Modal(document.getElementById('recordModal'));
				  $('.search-input').prop('placeholder', 'Filter Table'); //change 'Search' to 'Filter'

				  //popup for record
				  $(document).on('click', '.recordLink', function () {
				     let ai = $(this).attr('data-id')
				     $('.editRecord').attr('data-id', ai);
				     $('.deleteRecord').attr('data-id', ai);
				     var html = '<dl>';
				     $.getJSON('ajax.php?action=getRecord&ai='+ai, function (data) {
				       $.each(data, function (i, v) {
				         if (v == '' || v == 'null' || v == null) {
				           return;
				         }
				         if (i == 'hasTranscription') {
				           html += '<dt>transcription</dt><dd><a href="#">link</a>';
				         }
				         else if (v.substr(0,4) == 'http') {   //create <a> tags for links
				           v = '<a href="' + v + '" target="_blank">link</a>';
				         }
				         html += '<dt>' + i + '</dt>';
                         html += '<dd>' + v + '</dd>';
				       });
				     })
				     .done(function () {
				       html += '</dl>'
				       $('.modal-title').html(ai);
				       $('.modal-body').html(html);
				       myModal.show();
				     });
				  });

				  //edit record link clicked from modal
				  $(document).on('click', '.editRecord', function () {
				    let ai = $(this).attr('data-id');
				    let params = {
				      url: 'ajax.php?action=editRecord&ai='+ai,
				      dataType: 'html'
				    }
				    $.ajax(params, function() {
				    })
				    .done(function (data) {
				      $('#recordModal').removeClass('fade');
				      $('#recordModal').modal('hide');
				      $('#mainBody').html(data);
				    })
				  });
				  
				  //delete record link clicked from modal
				  $(document).on('click', '.deleteRecord', function () {
				    let ai = $(this).attr('data-id');
				    if (!confirm('Are you sure you want to delete record '+ai)) {
				      return;
				    }	    
				    let params = {
				      url: 'ajax.php?action=deleteRecord&ai='+ai
				    }
				    $.ajax(params, function() {
				    })
				    .done(function () {
				      alert('Record deleted');
				      location.reload();
				    })
				  });

				});
			</script>
HTML;
	}

	private function _writeSearchJavascript() {
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
				    $.getJSON('ajax.php?action=getDropdownOptions&field='+field, function (data) {
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
				    $.post('ajax.php', {action: "getSearchFieldDropdown", searchFieldCount: searchFieldCount}, function(data) {
				      var html = '<tr><td>' + booleanDropdown + '</td>';
				      html += '<td>' + data + '</td>';
				      html += '<td id="cell_'+searchFieldCount+'"><input type="text" class="form-control" placeholder="Search"';
				      html += ' id="s'+searchFieldCount+'" aria-label="Search" name="s[' + searchFieldCount + ']" autofocus required></td>';
				      html += '<td><button type="button" class="btn btn-primary addRow"><i class="fas fa-plus"></i></button></td></tr>';
				      $('#searchElements').append(html);
				      searchFieldCount ++;
				    });
				  });

				  $('#regex').on('click', function() {
				    setSearchParams(this.checked); 
				  }); 
				  
				  $('form').on('submit', function (event) {
				    event.preventDefault();
				  });

				  $('#resetForm').on('click', function () {
				    $.ajax('ajax.php?action=resetSearchForm')
				    .done(function () {
				      window.location.href = "?m=records&a=search";
				    });
				  });
					
				  /*
				    Save the search form data to reload when back is clicked
				  */
				  $('#searchSubmit').on('click', function () {
				    let form = $('#searchForm');
				    let url = 'ajax.php?action=saveSearchForm';
				    let data = form.serialize();
				    $.ajax({
				      url: url,
				      method: 'post',
				      data: data,
				      success: function (response) {
				        if (response == true) {          
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
					    //$('#accins').attr("checked", true);
					    $('#lenins').attr("disabled", false);
					    //$('#lenins').attr("checked", true);
					    $('#exact').attr("disabled", false);
					    $('#exact').attr("checked", false);
				    }
				}
			</script>
HTML;
	}
}