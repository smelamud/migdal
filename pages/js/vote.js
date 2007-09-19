function voteClick(event) {
    href = "/ajax" + $(this).attr("href");
    id = href.split("/")[4];
    $("#rating-" + id).removeClass().addClass("rating-zero").text("...");
    $.getJSON(href,
        function(data) {
            if (data.rating == 0) {
                className = "rating-zero";
            } else if (data.rating > 0) {
                className = "rating-plus";
                data.rating = "+" + data.rating;
            } else {
                className = "rating-minus";
            }
            $("#rating-" + data.id).removeClass().addClass(className)
                .text(data.rating);
            if (data.vote > 3) {
                $("#vote-minus-" + data.id).css("visibility", "hidden");
                button = $("#vote-plus-" + data.id);
                button[0].src = "/pics/vote-plus-gray.gif";
            } else {
                $("#vote-plus-" + data.id).css("visibility", "hidden");
                button = $("#vote-minus-" + data.id);
                button[0].src = "/pics/vote-minus-gray.gif";
            }
            button.parent().parent().empty().append(button);
    });
    event.preventDefault();
}

function voteInit() {
    $(".vote-button").parent().click(voteClick);
}

$(voteInit)
