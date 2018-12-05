function helpTextInit() {
    $(".help-text").click(function (event) {
        var id = "#" + $(this).data("id");
        if ($(id).css("display") == "none") {
            $(id).show();
            $(this).text("Скрыть подсказку по форматированию текста");
            if (!$(id).data("loaded")) {
                $(id).load("/api/help/text", function () {
                    $(id).data("loaded", true);
                });
            }
        } else {
            $(id).hide();
            $(this).text("Показать подсказку по форматированию текста");
        }
        event.preventDefault();
    });
}

$(helpTextInit);
