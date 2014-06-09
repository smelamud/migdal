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
        }
    });
}

$(enlargeInit);
