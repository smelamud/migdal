<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/ident.php');
require_once('lib/array.php');
require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('grp/mailtypes.php');

function sendMail($type_id,$userId,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
mysql_query("insert into mailings(type_id,receiver_id,link)
             values($type_id,$userId,$link)")
  or sqlbug('Ошибка SQL при регистрации почтового сообщения');
}

function sendMailAdmin($type_id,$admin,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
mysql_query("insert into mailings(type_id,receiver_id,link)
             select $type_id,id,$link
	     from users
	     where $admin<>0")
  or sqlbug('Ошибка SQL при администраторской рассылке');
}

class Mailing
      extends DataObject
{
var $id;
var $type_id;
var $receiver_id;
var $text;
var $email;
var $email_disabled;

function Mailing($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getReceiverId()
{
return $this->receiver_id;
}

function getLink()
{
return $this->link;
}

function getEmail()
{
return $this->email;
}

function isEmailDisabled()
{
return $this->email_disabled;
}

}

require_once('grp/mailings.php');

class MailingsExtractIterator
      extends ArrayIterator
{

function MailingsExtractIterator()
{
global $mailingClassNames;

mysql_query('lock tables mailings write,users read');
$result=mysql_query('select mailings.id as id,type_id,receiver_id,link,
			    email,email_disabled
		     from mailings
			  left join users
			       on mailings.receiver_id=users.id');
$mails=array();
while($row=mysql_fetch_assoc($result))
     {
     $name=$mailingClassNames[$row['type_id']];
     $mails[]=new $name($row);
     }
mysql_query('delete from mailings');
mysql_query('unlock tables');
$this->ArrayIterator($mails);
}

}
?>
