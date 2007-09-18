function voteClick(event) {
    $.getJSON("/ajax" + $(this).attr("href"),
        function(data) {
            if (data.err) {
                return;
            }
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
    });
    event.preventDefault();
}

function voteInit() {
    $(".vote-button").parent().click(voteClick);
}

$(voteInit)
