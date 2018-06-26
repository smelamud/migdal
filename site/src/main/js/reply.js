function userLink(userName, guestName) {
    if (userName) {
        return "<user name=\"" + userName + "\">";
    }
    return "<user guest-name=\"" + guestName + "\">";
}

function replyInit() {
    $("a.forum-reply").click(function(event) {
        var comment = $(this).parent().parent().parent();
        var body = comment.find(".body").html().trim();
        var userName = comment.find(".header .name").html();
        var guestName = comment.find(".header .guest-name").html();

        body = body.replace(/<a href="mailto:[^"]+">([^<]+)<\/a>/g, "$1")
                   .replace(/<a class="name"[^>]*>([^<]+)<\/a>/g, "<user name=\"$1\">")
                   .replace(/<span class="guest-name">([^<]+)<\/span>/g, "<user guest-name=\"$1\">")
                   .replace(/<div class="quote">/g, "<quote>")
                   .replace(/<\/p>\s*<\/div>/g, "</quote>")
                   .replace(/<\/div>/g, "</quote>")
                   .replace(/<p>/g, "")
                   .replace(/<\/p>$/, "")
                   .replace(/<\/p>/g, "\n\n")
                   .replace(/<br>/g, "\n");
        body = "<quote>" + userLink(userName, guestName) + " пишет:\n\n" + body + "</quote>";

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
