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
            this.title = $(this.element).attr('data-title-large');
        }
    });
}

$(enlargeInit);
