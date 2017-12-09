function userInfoContent(id) {
    var data = window.userInfo[id];
    data.lastOnlineFuzzy = fuzzyTimeFormatElapsed(toOurtime(data.lastOnline));
    return Mustache.render($("#client-user-info-hbs-html").html(), data);
}

function userInfoInit(root) {
    var elements = "a.name";
    if (root) {
        elements = root + ' ' + elements;
    }
    $(elements).qtip({
        content: {
            text: function(event, api) {
                var id = $(this).attr("data-id");
                if (!window.userInfo) {
                    window.userInfo = {};
                }
                if (window.userInfo[id]) {
                    return userInfoContent(id);
                }
                $.getJSON("/api/user/" + id, function(data) {
                    window.userInfo[id] = data;
                    api.set("content.text", userInfoContent(id)); 
                });
            }
        },
        style: {
            widget: true,
            def: false,
            classes: "qtip-shadow userinfo-tooltip"
        },
        position: {
            adjust: {
                method: "flip flip"
            },
            viewport: $(window)
        }
    });
}

$(function() {
    userInfoInit();
});
