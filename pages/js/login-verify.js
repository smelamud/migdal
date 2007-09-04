function updateButton() {
    $("#login-button").attr("disabled", $("#login").val() == "");
}

function loginVerifyInit() {
    updateButton();
    $("#login").keyup(function() {
        updateButton();
    });
}

$(loginVerifyInit)
