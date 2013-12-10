<?php
# @(#) $Id$

require_once('lib/time.php');
require_once('lib/text.php');
require_once('lib/calendar.php');

// FIXME what to do with English?
function formatFuzzyTimeElapsed($time, $format/*for future extensions*/ = '') {
    $diff = ourtime() - $time;
    if ($diff < 60)
        return 'только что';
    $diff = (int)($diff / 60);
    if ($diff == 1)
        return 'минуту назад';
    if ($diff < 60)
        return $diff.getPlural($diff, array(' минуту', ' минуты', ' минут')).
               ' назад';
    $diff = (int)($diff / 60);
    if ($diff == 1)
        return 'час назад';
    if ($diff < 24)
        return $diff.getPlural($diff, array(' час', ' часа', ' часов')).
               ' назад';
    $diff = (int)($diff / 24);
    if ($diff == 1)
        return 'вчера';
    if ($diff == 2)
        return 'позавчера';
    if ($diff < 30)
        return $diff.getPlural($diff, array(' день', ' дня', ' дней')).
               ' назад';
    if ($diff < 60)
        return 'два месяца назад';
    if ($diff < 90)
        return 'три месяца назад';
    $day = gmdate('d ', $time);
    $year = gmdate('Y', $time);
    if ($year == gmdate('Y', ourtime()))
        return $day.formatRussianDate('k', $time);
    return $day.formatRussianDate('k', $time)." $year г.";
}
?>
