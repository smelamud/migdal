function enlargeInit() {
    $(".enlargeable").fancybox({
        closeExisting: true,
        buttons: [
            "zoom",
            "slideShow",
            "fullScreen",
            "download",
            "thumbs",
            "close"
        ],
        idleTime: 0,
        padding: 0,

        helpers: {
            title: {
                type: 'inside'
            }
        },

        caption: function() {
            var titleId = $(this).data("title-large-id");
            if (titleId) {
                return $("#" + titleId).html();
            } else {
                return $(this).data("title-large");
            }
        },

        afterShow: function() {
            userInfoInit(".fancybox-caption");
            voteInit(".fancybox-caption");
        },

        clickContent: "nextOrClose",
        dblclickContent: "zoom"
    });
}

$(enlargeInit);
