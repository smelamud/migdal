<?php
# @(#) $Id$

function sendMail($type,$userId,$link=0)
{
$type_id=getMailingTypeIdByIdent($type);
mysql_query("insert into mailings(type_id,receiver_id,link)
             values($type_id,$userId,$link)")
     or die('������ SQL ��� ����������� ��������� ���������');
}

function getMailingTypeIdByIdent($type)
{
$result=mysql_query("select id
                     from mailing_types
		     where ident='$type'")
	     or die('������ SQL ��� ������� ���� ��������� ���������');
return mysql_result($result,0,0);
}
?>
