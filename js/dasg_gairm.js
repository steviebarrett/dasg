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
	//$.getJSON("http://localhost/~stephenbarrett/dasg/ajax/gairm.php?action=getTranscription&path="+path, function(data) {		//for LOCAL development only
	$.getJSON("/ajax/gairm.php?action=getTranscription&path="+path, function(data) {
		let transcription = data.transcription;
		if (search = $('#pdfViewer').attr('data-search')) {
			var regex = new RegExp('(' + search + ')', 'gi');
			transcription = transcription.replace(regex, '<span class="hi">$1</span>');
		}
		$('#transcription').html(transcription);
	});
}