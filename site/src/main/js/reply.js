function replyInit() {
    $("a.forum-reply").click(function(event) {
        var body = $(this).parent().parent().children(".body").html().trim();
        var textarea = $("#comment-add:first");
        textarea.val(body + "\n\n" + textarea.val());
        textarea.focus();
        textarea.prop("selectionStart", body.length + 2);
        textarea.prop("selectionEnd", body.length + 2);
        event.preventDefault();
    })
}

$(function() {
    replyInit();
});
