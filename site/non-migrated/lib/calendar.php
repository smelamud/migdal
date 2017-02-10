<?php
# @(#) $Id$

require_once('lib/time.php');

$calendarAvailableCache = -1;

function isCalendarAvailable() {
    global $calendarAvailableCache;

    if ($calendarAvailableCache < 0)
        $calendarAvailableCache = array_search('calendar',
                                               get_loaded_extensions(),
                                               false);
    return $calendarAvailableCache;
}

if (!isCalendarAvailable()) {
    include('lib/jewish-calendar.php');
    include('lib/gregor-calendar.php');
    include('lib/unix-calendar.php');
}

include('lib/jewish-calendar-utils.php');

require_once('conf/migdal.conf');

require_once('lib/calendar-tables.php');

function getJewishFromDate($month, $day, $year) {
    global $jewMonthRL;

    getJewishFromDateDetailed($month, $day, $year, $jmonth, $jday, $jyear);
    return $jday.' '.$jewMonthRL[$jmonth].' '.$jyear;
}

function getRussianMonth($month) {
    global $rusMonthRL;

    return $rusMonthRL[$month];
}

function getCalendarAge($bMonth, $bDay, $bYear, $month, $day, $year) {
    if (isCalendarAvailable()) {
        $jbt = GregorianToJD($bMonth, $bDay, $bYear);
        $jt = GregorianToJD($month, $day, $year);
    } else {
        $jbt = GregorianToSDN($bMonth, $bDay, $bYear);
        $jt = GregorianToSDN($month, $day, $year);
    }
    return (int)(($jt - $jbt) / 365.25);
}

function formatEnglishDate($f, $time = 0) {
    return $time != 0 ? date($f,$time) : date($f);
}

function formatRussianDate($f, $time = 0) {
    global $rusDow3, $rusDow3L, $rusDow2, $rusDow2L, $rusDow, $rusDowL,
           $rusMonth3, $rusMonth3L, $rusMonthI, $rusMonthIL, $rusMonthR,
           $rusMonthRL;

    switch ($f) {
        case 'c':
            return $rusDow3L[formatEnglishDate('w', $time)];
        case 'C':
            return $rusDowL[formatEnglishDate('w', $time)];
        case 'D':
            return $rusDow3[formatEnglishDate('w', $time)];
        case 'e':
            return $rusDow2[formatEnglishDate('w', $time)];
        case 'E':
            return $rusDow2L[formatEnglishDate('w', $time)];
        case 'f':
            return $rusMonthR[formatEnglishDate('n', $time)];
        case 'F':
            return $rusMonthI[formatEnglishDate('n', $time)];
        case 'J':
            return $rusMonth3L[formatEnglishDate('n', $time)];
        case 'k':
            return $rusMonthRL[formatEnglishDate('n', $time)];
        case 'K':
            return $rusMonthIL[formatEnglishDate('n', $time)];
        case 'l':
            return $rusDow[formatEnglishDate('w', $time)];
        case 'M':
            return $rusMonth3[formatEnglishDate('n', $time)];
        case 'S':
            return 'ое';
        default:
            return formatEnglishDate($f, $time);
    }
}

function formatJewishDate($f, $time=0) {
    global $jewDow3, $jewDow3L, $jewDow, $jewDowL, $jewMonth3, $jewMonth3L,
           $jewMonthI, $jewMonthIL, $jewMonthR, $jewMonthRL;

    switch ($f) {
        case 'c':
            return $jewDow3L[formatEnglishDate('w', $time)];
        case 'C':
            return $jewDowL[formatEnglishDate('w', $time)];
        case 'd':
            $jday = formatJewishDate('j', $time);
            return strlen($jday) == 1 ? "0$jday" : $jday;
        case 'D':
            return $jewDow3[formatEnglishDate('w', $time)];
        case 'f':
            return $jewMonthR[formatJewishDate('v', $time)];
        case 'F':
            return $jewMonthI[formatJewishDate('v', $time)];
        case 'j':
            getJewishFromUNIXDetailed($time, $jmonth, $jday, $jyear);
            return $jday;
        case 'J':
            return $jewMonth3L[formatJewishDate('v', $time)];
        case 'k':
            return $jewMonthRL[formatJewishDate('v', $time)];
        case 'K':
            return $jewMonthIL[formatJewishDate('v', $time)];
        case 'l':
            return $jewDow[formatEnglishDate('w', $time)];
        case 'L':
            return isLeapJewishYear(formatJewishDate('Y', $time)) ? 1 : 0;
        case 'm':
            $jmonthi = formatJewishDate('n', $time);
            return strlen($jmonth) == 1 ? "0$jmonth" : $jmonth;
        case 'M':
            return $jewMonth3[formatJewishDate('v', $time)];
        case 'n':
            getJewishFromUNIXDetailed($time, $jmonth, $jday, $jyear);
            return $jmonth;
        case 'q':
            return getJewishYearDelay(formatJewishDate('Y', $time));
        case 'Q':
            return getJewishYearLength(formatJewishDate('Y', $time));
        case 'S':
            return 'ое';
        case 't':
            getJewishFromUNIXDetailed($time, $jmonth, $jday, $jyear);
            return getJewishMonthLength($jmonth, $jyear);
        case 'v':
            getJewishFromUNIXDetailed($time, $jmonth, $jday, $jyear);
            return ($jmonth != 6 || isLeapJewishYear($jyear)) ? $jmonth : 14;
        case 'y':
            return formatJewishDate('Y', $time) % 100;
        case 'Y':
            getJewishFromUNIXDetailed($time, $jmonth, $jday, $jyear);
            return $jyear;
        case 'z':
            return getJewishAbsoluteDayFromUNIX($time);
        default:
            return formatEnglishDate($f, $time);
     }
}

function formatEJewishDate($f, $time=0) {
    global $ejewDow3, $ejewDow3L, $ejewDow, $ejewDowL, $ejewMonth3,
           $ejewMonth3L, $ejewMonthI, $ejewMonthIL;

    switch ($f) {
         case 'c':
             return $ejewDow3L[formatEnglishDate('w', $time)];
         case 'C':
             return $ejewDowL[formatEnglishDate('w', $time)];
         case 'D':
             return $ejewDow3[formatEnglishDate('w', $time)];
         case 'F':
             return $ejewMonthI[formatJewishDate('v', $time)];
         case 'J':
             return $ejewMonth3L[formatJewishDate('v', $time)];
         case 'K':
             return $ejewMonthIL[formatJewishDate('v', $time)];
         case 'l':
             return $ejewDow[formatEnglishDate('w', $time)];
         case 'M':
             return $ejewMonth3[formatJewishDate('v', $time)];
         case 'S':
             $n = formatJewishDate('j', $time);
             return $n == 1 || $n == 21
                    ? 'st'
                    : ($n == 2 || $n == 22
                       ? 'nd'
                       : ($n == 3 || $n == 23
                          ? 'rd'
                          : 'th'));
         default:
             return formatJewishDate($f, $time);
     }
}

function formatAnyDate($f, $time = 0, $fix = true) {
    if ($fix)
        if ($time == 0)
            $time = ourtime();
    if (strlen($f) != 2)
        return formatEnglishDate($f, $time);
    else
        switch ($f[0]) {
            case 'E':
                return formatEnglishDate($f[1], $time);
            case 'R':
                return formatRussianDate($f[1], $time);
            case 'J':
                return formatJewishDate($f[1], $time);
            case 'I':
                return formatEJewishDate($f[1], $time);
        }
}
?>
