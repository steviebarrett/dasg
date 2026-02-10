
$(function() {
	$('.accentChar').on('click', function() {
		addCharacterToSearch($(this).text(), 'searchTerm');
	});
});
