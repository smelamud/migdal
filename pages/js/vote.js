function voteClick(event) {
    id = $(this).attr("data-id");
    href = "/ajax/actions/posting/" + id + "/vote/";
    $("#rating-" + id).removeClass().addClass("rating-zero").text("...");
    $.post(href,
        {
            postid: id,
            vote: $(this).attr("data-value")
        },
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
            button.parent().empty().append(button);
        }
    );
    event.preventDefault();
}

function voteInit() {
    $(".vote-active").click(voteClick);
}

$(voteInit)
