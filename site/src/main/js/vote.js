function voteClick(event) {
    id = $(this).attr("data-id");
    idClass = "rating-" + id;
    href = "/ajax/actions/posting/" + id + "/vote/";
    $("." + idClass).removeClass().addClass(idClass).addClass("rating-zero")
                    .text("...");
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
            var idClass = "rating-" + data.id;
            $("." + idClass).removeClass().addClass(idClass)
                            .addClass(className).text(data.rating);
            $(".small-" + idClass).removeClass().addClass("small-" + idClass)
                                  .addClass("small-" + className)
                                  .text("(" + data.rating + ")");
            if (data.vote > 3) {
                $(".vote-minus-" + data.id).css("visibility", "hidden");
                button = $(".vote-plus-" + data.id);
                button.attr("src", "/pics/vote-plus-gray.gif");
            } else {
                $(".vote-plus-" + data.id).css("visibility", "hidden");
                button = $(".vote-minus-" + data.id);
                button.attr("src", "/pics/vote-minus-gray.gif");
            }
            button.removeClass("vote-active").removeAttr("alt")
                  .removeAttr("title");
        }
    );
    event.preventDefault();
}

function voteInit(root) {
    var elements = ".vote-active";
    if (root) {
        elements = root + ' ' + elements;
    }
    $(elements).click(voteClick);
}

$(function() {
    voteInit();
});
