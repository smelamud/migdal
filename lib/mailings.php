<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/ident.php');
require_once('lib/array.php');
require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('grp/mailtypes.php');

function sendMail($type_id,$userId,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
sql("insert into mailings(type_id,receiver_id,link)
     values($type_id,$userId,$link)",
    'sendMail');
}

function sendMailAdmin($type_id,$admin,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
sql("insert into mailings(type_id,receiver_id,link)
     select $type_id,id,$link
     from users
     where $admin<>0",
    'sendMailAdmin');
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

sql('lock tables mailings write,users read,profiling write',
    get_method($this,'MailingsExtractIterator'),'lock');
$result=sql('select mailings.id as id,type_id,receiver_id,link,
		    email,email_disabled
	     from mailings
		  left join users
		       on mailings.receiver_id=users.id',
             get_method($this,'MailingsExtractIterator'),'select');
$mails=array();
while($row=mysql_fetch_assoc($result))
     {
     $name=$mailingClassNames[$row['type_id']];
     $mails[]=new $name($row);
     }
sql('delete from mailings',
    get_method($this,'MailingsExtractIterator'),'delete');
sql('unlock tables',
    get_method($this,'MailingsExtractIterator'),'unlock');
$this->ArrayIterator($mails);
}

}
?>
