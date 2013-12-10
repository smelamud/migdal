<?php
# @(#) $Id$

require_once('lib/time.php');
require_once('lib/text.php');
require_once('lib/calendar.php');

// FIXME what to do with English?
function formatFuzzyTimeElapsed($time, $format/*for future extensions*/ = '') {
    $diff = ourtime() - $time;
    if ($diff < 60)
        return '������ ���';
    $diff = (int)($diff / 60);
    if ($diff == 1)
        return '������ �����';
    if ($diff < 60)
        return $diff.getPlural($diff, array(' ������', ' ������', ' �����')).
               ' �����';
    $diff = (int)($diff / 60);
    if ($diff == 1)
        return '��� �����';
    if ($diff < 24)
        return $diff.getPlural($diff, array(' ���', ' ����', ' �����')).
               ' �����';
    $diff = (int)($diff / 24);
    if ($diff == 1)
        return '�����';
    if ($diff == 2)
        return '���������';
    if ($diff < 30)
        return $diff.getPlural($diff, array(' ����', ' ���', ' ����')).
               ' �����';
    if ($diff < 60)
        return '��� ������ �����';
    if ($diff < 90)
        return '��� ������ �����';
    $day = gmdate('d ', $time);
    $year = gmdate('Y', $time);
    if ($year == gmdate('Y', ourtime()))
        return $day.formatRussianDate('k', $time);
    return $day.formatRussianDate('k', $time)." $year �.";
}
?>
