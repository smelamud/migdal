function reloginInit() {
    var captcha = document.getElementById("captcha");
    var rg = document.getElementById("relogin-guest");
    if (!rg || !rg.checked) {
        captcha.style.display = "none";
    } else {
        captcha.style.display = "";
    }
}

function reloginSelected(button) {
    var captcha = document.getElementById("captcha");
    if (button.id != "relogin-guest") {
        captcha.style.display = "none";
    } else {
        captcha.style.display = "";
    }
}
