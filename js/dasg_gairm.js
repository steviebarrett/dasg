/**
 * Code for the Gairm Index
 */

var bpopup;

$(function() {
	sortTable();

	$('.gairmViewLink').on('click', function() {
		var id = $(this).attr('id').split('_');
		showRecord(id[1]);
		return false;
	});

	$('.gairmRecordClose').on('click', function() {
		bpopup.close();
	});
	
	//show About Gairm popup
	$('#aboutGairmLink').on('click', function () {
		bpopup = $('#gairmAbout').bPopup({
			modal: true
		});
	});
	//hide About Gairm popup
	$('#gairmAboutClose').on('click', function() {
		bpopup.close();
	});

	//show Acknowledgements popup
	$('#acknowledgeGairmLink').on('click', function () {
		bpopup = $('#gairmAcknowledge').bPopup({
			modal: true
		});
	});
	//hide Acknowledgements popup
	$('#gairmAcknowledgeClose').on('click', function() {
		bpopup.close();
	});

	//clear the search box
	$('#queryString').on('click', function () {
		$('#queryString').val('');
	});
});


function sortTable() {
	$("#gairmResultsTable").tablesorter({
		headers: {
			5: {
				sorter: false
			},
			6: {
				sorter: false
			}
		}
	});
}

function upperCaseString(input) {
	var result = input.replace(/([A-Z]+)/g, ",$1").replace(/^,/, "");
	var arr = result.split(",");
	return arr.join(' ');
}

function showRecord(id) {
	//$.getJSON("http://localhost/~stephenbarrett/dasg/ajax/gairm.php?action=getRecord&id="+id, function(data) {		//for LOCAL development only
	$.getJSON("/ajax/gairm.php?action=getRecord&id="+id, function(data) {
	var html = "<table><tbody>";
		$.each(data, function(field, val) {
			if (field != 'id' && val != "") {
				field = upperCaseString(field);
				html += '<tr><td class="gairmRecordLeftCol">' + field + ':</td><td class="gairmRecordRightCol">' + val + '</td></tr>';
			}
		});
		html += '</tbody></table>';
		html += '<a href="#" class="gairmRecordClose" onclick="javascript:bpopup.close();return false;">X</a>';
		$('#gairmRecord').html(html);
		bpopup = $('#gairmRecord').bPopup({
			modal: true
		});
	});
}

function writeTranscription(path) {
	/**
	 * Code for the Gairm Index
	 */

	var bpopup;

	$(function() {
		sortTable();

		$('.gairmViewLink').on('click', function() {
			var id = $(this).attr('id').split('_');
			showRecord(id[1]);
			return false;
		});

		$('.gairmRecordClose').on('click', function() {
			bpopup.close();
		});

		//show About Gairm popup
		$('#aboutGairmLink').on('click', function () {
			bpopup = $('#gairmAbout').bPopup({
				modal: true
			});
		});
		//hide About Gairm popup
		$('#gairmAboutClose').on('click', function() {
			bpopup.close();
		});

		//show Acknowledgements popup
		$('#acknowledgeGairmLink').on('click', function () {
			bpopup = $('#gairmAcknowledge').bPopup({
				modal: true
			});
		});
		//hide Acknowledgements popup
		$('#gairmAcknowledgeClose').on('click', function() {
			bpopup.close();
		});

		//clear the search box
		$('#queryString').on('click', function () {
			$('#queryString').val('');
		});
	});


	function sortTable() {
		$("#gairmResultsTable").tablesorter({
			headers: {
				5: {
					sorter: false
				},
				6: {
					sorter: false
				}
			}
		});
	}

	function upperCaseString(input) {
		var result = input.replace(/([A-Z]+)/g, ",$1").replace(/^,/, "");
		var arr = result.split(",");
		return arr.join(' ');
	}

	function showRecord(id) {
		//$.getJSON("http://localhost/~stephenbarrett/dasg/ajax/gairm.php?action=getRecord&id="+id, function(data) {		//for LOCAL development only
		$.getJSON("/ajax/gairm.php?action=getRecord&id="+id, function(data) {
			var html = "<table><tbody>";
			$.each(data, function(field, val) {
				if (field != 'id' && val != "") {
					field = upperCaseString(field);
					html += '<tr><td class="gairmRecordLeftCol">' + field + ':</td><td class="gairmRecordRightCol">' + val + '</td></tr>';
				}
			});
			html += '</tbody></table>';
			html += '<a href="#" class="gairmRecordClose" onclick="javascript:bpopup.close();return false;">X</a>';
			$('#gairmRecord').html(html);
			bpopup = $('#gairmRecord').bPopup({
				modal: true
			});
		});
	}

	function writeTranscription(path) {
		$.getJSON("/ajax/gairm.php?action=getTranscription&path="+path, function(data) {
			let transcription = (data && data.transcription != null) ? String(data.transcription) : '';
			const search = $('#pdfViewer').attr('data-search');

			// Helper: escape regex metacharacters for literal searches
			function escapeRegExp(s) {
				return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			}

			const $t = $('#transcription');

			// Always render transcription as TEXT (prevents DOM XSS)
			$t.empty();

			if (search) {
				const needle = escapeRegExp(search);
				let re;
				try {
					re = new RegExp(needle, 'gi');
				} catch (e) {
					re = null;
				}

				if (re) {
					// Build a safe fragment: text nodes + <span class="hi"> for matches
					let lastIndex = 0;
					let m;
					while ((m = re.exec(transcription)) !== null) {
						const start = m.index;
						const end = start + m[0].length;

						if (start > lastIndex) {
							$t.append(document.createTextNode(transcription.slice(lastIndex, start)));
						}

						const span = document.createElement('span');
						span.className = 'hi';
						span.textContent = m[0];
						$t.append(span);

						lastIndex = end;

						// Avoid infinite loops on zero-length matches
						if (re.lastIndex === start) {
							re.lastIndex++;
						}
					}

					if (lastIndex < transcription.length) {
						$t.append(document.createTextNode(transcription.slice(lastIndex)));
					}
				} else {
					$t.text(transcription);
				}
			} else {
				$t.text(transcription);
			}
		});
	}
}