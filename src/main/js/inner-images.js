function insertImageMouseMoved(event) {
    if (!window.imageEditing) {
        return;
    }

    var div = null;
    var minDiff = 100;
    $(".article P").each(function() {
        var diff = Math.abs(event.pageY - $(this).offset().top);
        if (diff <= 25 && diff < minDiff) {
            var dv = $(this).prev();
            if (dv.hasClass("insert-image")) {
                div = dv;
                minDiff = diff;
            }
        }
    });
    if (div == window.currentInsertImage) {
        return;
    }
    if (window.currentInsertImage != null) {
        window.currentInsertImage.css("visibility", "hidden");
    }
    if (div != null) {
        div.css("visibility", "visible");
    }
    window.currentInsertImage = div;
}

function insertImageMouseOut() {
    if (window.currentInsertImage != null) {
        window.currentInsertImage.css("visibility", "hidden");
        window.currentInsertImage = null;
    }
}

function imageEditingSwitched() {
    if (window.location.hash == "#image-editing") {
        $(".article").removeClass("article-image-editing-off").addClass("article-image-editing-on");
        $("#switch-image-editing-on").hide();
        $("#switch-image-editing-off").show();
        window.imageEditing = true;
    } else {
        $(".article").removeClass("article-image-editing-on").addClass("article-image-editing-off");
        $("#switch-image-editing-off").hide();
        $("#switch-image-editing-on").show();
        insertImageMouseOut();
        window.imageEditing = false;
    }
}

$(function() {
    imageEditingSwitched();
    window.currentInsertImage = null;
    $(".article").mousemove(insertImageMouseMoved)
                 .mouseout(insertImageMouseOut);
    $("#switch-image-editing-on").click(function(event) {
        window.location.hash = "#image-editing";
        imageEditingSwitched();
        event.preventDefault();
    })
    $("#switch-image-editing-off").click(function(event) {
        window.location.hash = "";
        imageEditingSwitched();
        event.preventDefault();
    })
});
