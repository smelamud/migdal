function enlargeInit() {
    $(".enlargeable").fancybox({
        animationEffect: "elastic",
        transitionEffect: "fade",
        padding: 0,

        helpers: {
            title: {
                type: 'inside'
            }
        },

        caption: function() {
            var title_id = $(this).attr('data-title-large-id');
            if (title_id) {
                return $("#" + title_id).html();
            } else {
                return $(this).attr('data-title-large');
            }
        },

        afterShow: function() {
            userInfoInit(".fancybox-caption");
            voteInit(".fancybox-caption");
        }
    });
}

$(enlargeInit);
