function reloginInit() {
    $(".relogin input[type=text]").keypress(function () {
        $(this).parent().find("input[type=radio]").click();
    });

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

    $(".relogin input[type=radio]").click(function () {
        if ($(this).attr("id") !== "relogin-guest") {
            $("#captcha").hide();
        } else {
            $("#captcha").show();
        }
    })
}

$(reloginInit);
