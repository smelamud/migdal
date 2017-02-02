// @(#) $Id$

function insertImageMouseMoved(event) {
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

$(function() {
    window.currentInsertImage = null;
    $(".article").mousemove(insertImageMouseMoved)
                 .mouseout(insertImageMouseOut);;
});
