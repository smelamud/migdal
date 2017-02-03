// @(#) $Id$

/* Time is in seconds of Unix epoch */

window.rusMonthRL = ["января", "февраля", "марта", "апреля", "мая", "июня",
                     "июля", "августа", "сентября", "октября", "ноября",
                     "декабря"];

function ourtime() {
    return Math.floor(new Date().getTime() / 1000);
}

function toOurtime(time) {
    return time + window.timeZoneDiff * 3600;
}

function fuzzyTimeFormatElapsed(time) {
    var diff = ourtime() - time;
    if (diff < 60)
        return "только что";
    diff = Math.floor(diff / 60);
    if (diff == 1)
        return "минуту назад";
    if (diff < 60)
        return diff + plural(diff, [" минуту", " минуты", " минут"]) + " назад";
    diff = Math.floor(diff / 60);
    if (diff == 1)
        return "час назад";
    if (diff < 24)
        return diff + plural(diff, [" час", " часа", " часов"]) + " назад";
    diff = Math.floor(diff / 24);
    if (diff == 1)
        return 'вчера';
    if (diff == 2)
        return 'позавчера';
    if (diff < 30)
        return diff + plural(diff, [" день", " дня", " дней"]) + " назад";
    if (diff < 60)
        return "два месяца назад";
    if (diff < 90)
        return "три месяца назад";
    var ourdate = new Date();
    var date = new Date(time * 1000);
    var day = date.getDate();
    var month = window.rusMonthRL[date.getMonth()];
    var year = date.getFullYear();
    if (year == ourdate.getFullYear())
        return day + " " + month;
    return day + " " + month + " " + year + " г.";
}

function fuzzyTimeUpdate() {
    $(".fuzzy-time").each(function() {
        var time = toOurtime(parseInt($(this).attr("data-time")));
        $(this).text(fuzzyTimeFormatElapsed(time));
    });
}

function fuzzyTimeInit() {
    fuzzyTimeUpdate();
    window.setInterval(fuzzyTimeUpdate, 60 * 1000);
}

$(fuzzyTimeInit);
