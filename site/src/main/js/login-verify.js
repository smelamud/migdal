function loginChanged() {
    $("#login-button").attr("disabled", $("#login").val() == "");
    $("#login-status").text("");
}

function loginVerify(event) {
    $("#login-status").removeClass().text("Идет проверка...");
    $.getJSON("/api/user/login/exists",
              {login: $("#login").val()},
              function(data) {
                  if (data.exists) {
                      $("#login-status").addClass("register-login-busy")
                          .text("Ник '" + data.login + "' уже зарегистрирован");
                  } else {
                      $("#login-status").addClass("register-login-avail")
                          .text("Ник '" + data.login + "' свободен");
                  }
              });
    event.preventDefault();
}

function loginVerifyInit() {
    loginChanged();
    $("#login").keyup(loginChanged);
    $("#login-button").click(loginVerify);
}

$(loginVerifyInit);