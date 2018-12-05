function moderatorInit() {
    $(".moderator-cell").click(function () {
        var checkbox = $(this).find("input");
        checkbox.prop("checked", !checkbox.prop("checked"));
    }).children().click(function (event) {
        event.stopPropagation();
    });

    $("#auto").click(function () {
        var unset = !!$(this).data("unset");
        $(this).data("unset", !unset);
        $(".moderator-attention").each(function () {
            if ($(this).find("input").prop("checked")) {
                $(this).parent().find(".moderator-spam").find("input").prop("checked", !unset);
            }
        });
    })
}

$(moderatorInit);
