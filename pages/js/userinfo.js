// @(#) $Id$

function userInfoContent(id) {
    var data = window.userInfo[id];
    var content = "<div class='fullname'>" + data.fullName + "</div>" +
                  "<div class='rank'>" + data.rank + "</div>" +
                  "<div class='last-online'>" +
                  (!data.femine ? "Заходил" : "Заходила") +
                  " сюда " + data.lastOnline +
                  "</div>"
    return content;
}

$(function() {
    $("a.name").each(function() {
        $(this).qtip({
            content: {
                text: function(event, api) {
                    var id = $(this).attr("data-id");
                    if (!window.userInfo) {
                        window.userInfo = {};
                    }
                    if (window.userInfo[id]) {
                        return userInfoContent(id);
                    }
                    $.getJSON("/ajax/sources/user/" + id, function(data) {
                        window.userInfo[id] = data;
                        api.set("content.text", userInfoContent(id)); 
                    });
                }
            },
            style: {
                widget: true,
                def: false,
                classes: "userinfo-tooltip"
            },
            position: {
                adjust: {
                    method: "flip flip"
                },
                viewport: $(window)
            }
        });
    });
});
