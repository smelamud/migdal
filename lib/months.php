<?php
# @(#) $Id$

$months=array(1 => '������','�������','�����','������','���','����',
              '����','�������','��������','�������','������','�������');

function getRussianMonth($month)
{
global $months;

return $months[$month];
}
?>
