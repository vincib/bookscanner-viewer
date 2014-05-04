
var deleting;
function delcard(i) {
    deleting=i;
    if (confirm('Merci de confirmer la suppression de cette fiche !')) {
	var ret = $.ajax({
	    url:            'delcard.php?id='+i,
	    type:           'GET',
	    cache:          false,
	    async:          false,
	    success: function(data) {
		if (data=="OK") {
		    $('#messagebox').html("<div id=\"al\" class=\"alert alert-success\">Fiche effacée</div>");
		} else {
		    $('#messagebox').html("<div id=\"al\" class=\"alert alert-success\">Fiche NON effacée ! ("+data+")</div>");
		}
		window.setTimeout(function() { $("#al").alert('close'); }, 2000);
		$("#hr"+deleting).remove();
	    }
	})
    }
    return false;
}