<?php
# @(#) $Id$

function incLogins()
{
mysql_query('update statistics set logins=logins+1')
     or die('������ SQL ��� ���������� ���������� �������');
}

?>
