$(document).ready(function() {

	$('#qsToggle').click('click', function() {
		$('#searchbox').toggle();
		$('#query').focus();
	});	

	$('#query').keypress(function (e) {
	  if (e.which == 13) {
	    searchCorpus($('#query').val());
	    return false; 
	  }
	});
	
	$('#searchCorpus').on("click", function() {
		searchCorpus($('#query').val());
	});
	
	$('#searchFieldwork').on("click", function() {
		var url = "http://dasg.ac.uk:8080/exist/apps/fieldwork/index.html?q=" + $("#query").val()
		window.location.replace(url);
	});
	
});

document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.accent-char').forEach(function (el) {

		el.addEventListener('click', function (e) {
			e.preventDefault();

			const char = this.dataset.char;
			addCharacterToSearch(char, 'query');
		});

	});

});

function setSearchParams(searchField, regexFlag) 
{
	updateLenition(searchField, regexFlag);
	updateAccents(searchField, regexFlag);
}

function updateAccents(searchField, regexFlag)
{
	if (!$('#accInsens').is(':checked')) 
		return false;
 
	var query = $('#'+searchField).val();
	var newQ = '';
	var delimiter = '';
	var unacc = "aeiou";
	var acutes = "àèìòù";
	var graves = "áéíóú";
	var hit, rpl;
	var rpl = Array();
	
	if (regexFlag == 0)
		delimiter = ',';

	for (i=0;i<query.length;i++) {
		var chr = query.charAt(i);
		
		if (unacc.indexOf(chr) > -1) {
			hit = unacc.indexOf(chr);
			rpl[i] = '[' + chr + delimiter + acutes.charAt(hit) + delimiter + graves.charAt(hit) + ']';		
			continue;		
		} 

		if (acutes.indexOf(chr) > -1) {
			hit = acutes.indexOf(chr);
			rpl[i] = '[' + chr + delimiter + unacc.charAt(hit) + delimiter + graves.charAt(hit) + ']';
			continue;				
		} 

		if (graves.indexOf(chr) > -1) {
			hit = graves.indexOf(chr);
			rpl[i] = '[' + chr + ',' + acutes.charAt(hit) + ',' + unacc.charAt(hit) + ']';
			continue;				
		} 

		rpl[i] = '';
	}

	for (x=0;x<rpl.length;x++) {
		if (rpl[x] == '')
			newQ += query.charAt(x);
		else
			newQ += rpl[x];
	}

	$('#'+searchField).val(newQ);
}


function updateLenition(searchField, regexFlag)
{
	var query = $('#'+searchField).val();
	var regex = '';
	
	if (regexFlag == 1)
		regex = 'h=';
	else 
		regex = '[h,]';

	if (!$('#lenited').is(':checked')) {
		//remove any previous lenition check syntax
		if (hasLenitedSyntax(query)) {
			query = query.replace("[h,]", "");
			$('#'+searchField).val(query);
		}
		return false;
	}
	
	var isLenited = false;
	
	if (canBeLenited(query) == false)
		return false;
		
	var lenitionQuery = query;
	
	if (query.charAt(1) == 'h')
		lenitionQuery = query.slice(0,1) + query.slice(2,query.length);
		
	lenitionQuery = query.charAt(0) + regex + lenitionQuery.slice(1,query.length);
	
	$('#'+searchField).val(lenitionQuery);
}

function canBeLenited(word)
{
	if (word.length < 2)
		return false;

	excludeChars = new Array('h', 'l', 'n', 'r', '?', '*', '~', '[', ']');
	if ($.inArray(word.charAt(0), excludeChars) > -1)
		return false;
		
	if (hasLenitedSyntax(word))
		return false;
		
	return true;
}

function hasLenitedSyntax(word)
{
	if (word.substr(1,4) == "[h,]")
		return true;
}

function searchCorpus(query)
{
  	var url = "http://dasg.ac.uk/corpus_wip/dasg/concordance.php?theData=" + query + "&qmode=sq_nocase&pp=50&del=end&uT=y";
  	window.location.replace(url);
}
  			
function addCharacterToSearch(char, searchBox)
{	
	$('#'+searchBox).focus();
	var caretPosition = $('#'+searchBox).caret();
	
	var searchText = $('#'+searchBox).val();

	var beforeCursorText = searchText.substring(0, caretPosition);
	var afterCursorText = searchText.substring(caretPosition, searchText.length);
	
	caretPosition++;
	
	searchText = beforeCursorText + char + afterCursorText;

	$('#'+searchBox).val(searchText);
	$('#'+searchBox).caret(caretPosition);
}

function stripslashes(str) 
{
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

function setCookie(c_name, value, exdays) {
	var exdate = new Date();
	if (exdays != null) {
		exdate.setDate(exdate.getDate() + exdays);
	}

	// Build cookie attributes
	var attrs = '; path=/; SameSite=Lax';
	if (location && location.protocol === 'https:') {
		attrs += '; Secure';
	}

	// Delete old cookie (match attributes so deletion works reliably)
	document.cookie = encodeURIComponent(c_name) + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT' + attrs;

	// Set new cookie
	var c_value = encodeURIComponent(String(value)) +
		(exdays == null ? '' : '; expires=' + exdate.toUTCString()) +
		attrs;

	document.cookie = encodeURIComponent(c_name) + '=' + c_value;
}

function getCookie(c_name) {
	var name = encodeURIComponent(c_name) + '=';
	var parts = document.cookie.split(';');

	for (var i = 0; i < parts.length; i++) {
		var p = parts[i].replace(/^\s+/, '');
		if (p.indexOf(name) === 0) {
			return decodeURIComponent(p.substring(name.length));
		}
	}
	return null;
}

function urldecode(str) 
{
	return decodeURIComponent((str+'').replace(/\+/g, '%20'));
}

