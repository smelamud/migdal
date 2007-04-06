function newWindow(page, name, w, h, scroll, pos) {
    var win=null;

    if(pos == "random") {
        leftPosition = screen.width
                ? Math.floor(Math.random() * (screen.width - w))
                : 100;
        topPosition = screen.height
                ? Math.floor(Math.random() * (screen.height - h - 75))
                : 100;
    }
    if(pos == "center") {
        leftPosition = screen.width ? (screen.width - w) / 2 : 100;
        topPosition = screen.height ? (screen.height - h) / 2 : 100;
    }
    if(pos != "center" && pos != "random" || pos == null) {
        leftPosition = 0;
        topPosition = 20;
    }
    settings = 'width=' + w + ',height=' + h + ',top=' + topPosition
            + ',left=' + leftPosition + ',scrollbars=' + scroll
            + ',location=no,directories=no,status=yes,menubar=no,toolbar=no,resizable=no';
    win = window.open(page, name, settings);
    if(win.focus) {
        win.focus();
    }
}

function userInfo(id) {
    newWindow('/users/' + id + '/panel/', 'info', '400', '400', 'auto',
            'random');
}
