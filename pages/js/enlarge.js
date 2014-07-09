function enlargeInit() {
    $(".enlargeable").fancybox({
        openEffect: 'elastic',
        closeEffect: 'elastic',
        nextEffect: 'fade',
        prevEffect: 'fade',

        helpers: {
            title: {
                type: 'inside'
            }
        },

        beforeLoad: function() {
            var title_id = $(this.element).attr('data-title-large-id');
            if (title_id) {
                this.title = $("#" + title_id).html();
            } else {
                this.title = $(this.element).attr('data-title-large');
            }
        },

        afterShow: function() {
            userInfoInit(".fancybox-title");
            voteInit(".fancybox-title");
        }
    });
}

$(enlargeInit);
