<?php
# @(#) $Id$

require_once('conf/migdal.conf');

function ourtime() {
    global $timeZone;

    $time = explode(',', gmdate('H,i,s,m,d,Y', time()));
    return mktime($time[0], $time[1], $time[2], $time[3], $time[4], $time[5])
           + $timeZone * 3600;
}

function composeDateTime($timestamp, $vars, $base_name) {
    $comps = array('seconds' => 'second',
                   'minutes' => 'minute',
                   'hours' => 'hour',
                   'mday' => 'day',
                   'mon' => 'month',
                   'year' => 'year');
    $dt = getdate($timestamp);
    foreach ($comps as $comp => $varname) {
        $name = "${base_name}_$varname";
        if (isset($vars[$name])
            && ($vars[$name] > 0
                || $comp[strlen($comp) - 1] == 's' && $vars[$name] >= 0))
            $dt[$comp] = $vars[$name];
    }
    $ts = mktime($dt['hours'], $dt['minutes'], $dt['seconds'],
                 $dt['mon'], $dt['mday'], $dt['year']);
    if ($ts === false || $ts === -1)
        return ourtime();
    else
        return $ts;
}

function getTimestamp($year = 0, $month = 0, $day = 0, $hour = -1,
                      $minute = -1, $second = -1) {
    $vars = array('_year'   => $year,
                  '_month'  => $month,
                  '_day'    => $day,
                  '_hour'   => $hour,
                  '_minute' => $minute,
                  '_second' => $second);
    return composeDateTime(ourtime(), $vars, '');
}
?>