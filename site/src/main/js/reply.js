function replyInit() {
    $("a.forum-reply").click(function(event) {
        var body = $(this).parent().parent().children(".body").html().trim();
        body = body.replace(/<a href="mailto:[^"]+">([^<]+)<\/a>/g, "$1")
                   .replace(/<div class="quote">/g, "<quote>")
                   .replace(/<\/p>\s*<\/div>/g, "</quote>")
                   .replace(/<\/div>/g, "</quote>")
                   .replace(/<p>/g, "")
                   .replace(/<\/p>$/, "")
                   .replace(/<\/p>/g, "\n\n")
                   .replace(/<br>/g, "\n");
        body = "<quote>" + body + "</quote>";
        var textarea = $("#comment-add");
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
