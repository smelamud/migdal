<?php
# @(#) $Id$

function sendMail($type,$userId,$link=0)
{
$type_id=getMailingTypeIdByIdent($type);
mysql_query("insert into mailings(type_id,receiver_id,link)
             values($type_id,$userId,$link)")
     or die('Ошибка SQL при регистрации почтового сообщения');
}

function sendMailAdmin($type,$admin,$link=0)
{
$type_id=getMailingTypeIdByIdent($type);
mysql_query("insert into mailings(type_id,receiver_id,link)
             select $type_id,id,$link
	     from users
	     where $admin<>0")
     or die('Ошибка SQL при администраторской рассылке');
}

function getMailingTypeIdByIdent($type)
{
$result=mysql_query("select id
                     from mailing_types
		     where ident='$type'")
	     or die('Ошибка SQL при выборке типа почтового сообщения');
return mysql_result($result,0,0);
}
?>
