function moderatorInit() {
    $(".moderator-cell").click(function () {
        var checkbox = $(this).find("input");
        checkbox.prop("checked", !checkbox.prop("checked"));
    }).children().click(function (event) {
        event.stopPropagation();
    });
}

$(moderatorInit);
