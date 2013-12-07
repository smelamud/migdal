function reloginInit() {
    var rg = $("#relogin-guest");
    if (rg.size() > 0) {
    	rg.each(function() {
	    if (!this.checked) {
		$("#captcha").hide();
	    } else {
		$("#captcha").show();
	    }
	});
    } else {
	$("#captcha").hide();
    }
}

function reloginSelected(button) {
    if (button.id != "relogin-guest") {
        $("#captcha").hide();
    } else {
        $("#captcha").show();
    }
}

$(reloginInit);
