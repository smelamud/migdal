function gotoPage(event) {
    event.preventDefault();

    var pageSize = parseInt($(this).data("page-size"));
    var totalPages = parseInt($(this).data("total-pages"));
    var page = parseInt($(this).children("[name=value]").val());

    if (isNaN(page) || page < 1 || page > totalPages) {
        return;
    }

    window.location = URI(window.location)
                            .removeSearch("offset")
                            .removeSearch("tid")
                            .addSearch("offset", (page - 1) * pageSize)
                            .toString();
}

function gotoPageInit() {
    $(".goto-page").submit(gotoPage);
}

$(gotoPageInit);