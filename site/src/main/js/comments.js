function userLink(userName, guestName) {
    if (userName) {
        return "<user name=\"" + userName + "\">";
    }
    return "<user guest-name=\"" + guestName + "\">";
}

function replyInit() {
    $("a.comment-reply").click(function(event) {
        var comment = $(this).parent().parent().parent();
        var userName = comment.find(".header .name").html();
        var guestName = comment.find(".header .guest-name").html();

        var body = window.getSelection().toString();
        if (!body) {
            body = comment.find(".body").html().trim();
            body = body.replace(/[\r\n]/g, "")
                       .replace(/<a href="mailto:[^"]+">([^<]+)<\/a>/g, "$1")
                       .replace(/<a class="name"[^>]*>([^<]+)<\/a>/g, "<user name=\"$1\">")
                       .replace(/<span class="guest-name">([^<]+)<\/span>/g, "<user guest-name=\"$1\">")
                       .replace(/<div class="quote">/g, "<quote>")
                       .replace(/<\/p>\s*<\/div>/g, "</quote>")
                       .replace(/<\/div>/g, "</quote>")
                       .replace(/<p>/g, "")
                       .replace(/<\/p>$/, "")
                       .replace(/<\/p>/g, "\n\n")
                       .replace(/<br>/g, "\n")
                       .replace(/^\n+/, "");
        }
        body = "<quote>" + userLink(userName, guestName) + " пишет:\n\n" + body + "</quote>";

        var textarea = $("#comment-add");
        textarea.val(textarea.val() + body + "\n\n");
        textarea.focus();
        var len = textarea.val().length;
        textarea.prop("selectionStart", len);
        textarea.prop("selectionEnd", len);

        event.preventDefault();
    })
}

function commentsSectionInit() {
    commentsHashChanged();
    $(window).on("hashchange", commentsHashChanged);
}

function commentsHashChanged() {
    var threadRegex = /^#t\d+$/;
    if (location.hash == "#comments" || location.hash == "#comment-add" || location.hash.match(threadRegex)) {
        $("#comments-section").show();
        var name = location.hash.substring(1);
        $("a[name=" + name + "]")[0].scrollIntoView(true);
    }
}

function commentsInit() {
    replyInit();
    commentsSectionInit();
}

$(function() {
    commentsInit();
});
