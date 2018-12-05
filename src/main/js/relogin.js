function reloginInit() {
    $(".relogin input[type=text]").keypress(function () {
        $(this).parent().find("input[type=radio]").click();
    });
}

$(reloginInit);
