<?php
# @(#) $Id$

require_once('lib/ident.php');
require_once('lib/array.php');
require_once('lib/dataobject.php');

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

class Mailing
      extends DataObject
{
var $id;
var $link;
var $receiver_id;
var $text;
var $email;
var $email_disabled;
var $force_send;

function Mailing($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getLink()
{
return $this->link;
}

function getReceiverId()
{
return $this->receiver_id;
}

function getText()
{
return $this->text;
}

function getEmail()
{
return $this->email;
}

function isEmailDisabled()
{
return $this->email_disabled;
}

function isForceSend()
{
return $this->force_send;
}

}

class MailingsExtractIterator
      extends ArrayIterator
{

function MailingsExtractIterator()
{
mysql_query('lock tables mailings write,mailing_types read,users read');
$result=mysql_query('select mailings.id as id,link,receiver_id,text,
			    email,email_disabled,force_send
		     from mailings
			  left join mailing_types
			       on mailings.type_id=mailing_types.id
			  left join users
			       on mailings.receiver_id=users.id');
$mails=array();
while($row=mysql_fetch_assoc($result))
     $mails[]=new Mailing($row);
mysql_query('delete from mailings');
mysql_query('unlock tables');
$this->ArrayIterator($mails);
}

}
?>
