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
    $diff /= 60;
    if ($diff == 1)
        return '������ �����';
    if ($diff < 60)
        return getPlural($diff, array('������', '������', '�����')).' �����';
    $diff /= 60;
    if ($diff == 1)
        return '��� �����';
    if ($diff < 24)
        return getPlural($diff, array('���', '����', '�����')).' �����';
    $diff /= 24;
    if ($diff == 1)
        return '�����';
    if ($diff == 2)
        return '���������';
    if ($diff < 30)
        return getPlural($diff, array('����', '���', '����')).' �����';
    if ($diff < 60)
        return '��� ������ �����';
    if ($diff < 90)
        return '��� ������ �����';
    if (gmdate('Y', $time) == gmdate('Y', ourtime()))
        return formatRussianDate('d k', $time);
    return formatRussianDate('d k Y', $time).' �.';
}
?>
