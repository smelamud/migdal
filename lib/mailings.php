<?php
# @(#) $Id$

require_once('lib/ident.php');

function sendMail($type,$userId,$link=0)
{
$type_id=idByIdent('mailing_types',$type);
mysql_query("insert into mailings(type_id,receiver_id,link)
             values($type_id,$userId,$link)")
     or die('Ошибка SQL при регистрации почтового сообщения');
}

function sendMailAdmin($type,$admin,$link=0)
{
$type_id=idByIdent('mailing_types',$type);
mysql_query("insert into mailings(type_id,receiver_id,link)
             select $type_id,id,$link
	     from users
	     where $admin<>0")
     or die('Ошибка SQL при администраторской рассылке');
}
?>
