function plural(n, forms) {
    var a = n % 10;
    var b = Math.floor(n / 10) % 10;
    return b === 1 || a >= 5 || a === 0
           ? forms[2]
           : (a === 1 ? forms[0] : forms[1]);
}

function he(s) {
    return $("<div/>").text(s).html();
}


function hd(s) {
    return $("<div/>").html(s).text();
}
