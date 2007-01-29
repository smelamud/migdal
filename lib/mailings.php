<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/ident.php');
require_once('lib/array.php');
require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('grp/mailtypes.php');

function sendMail($typeId,$userId,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
sql("insert into mailings(type_id,receiver_id,link)
     values($typeId,$userId,$link)",
    __FUNCTION__);
}

function sendMailAdmin($typeId,$admin,$link=0)
{
global $replicationMaster;

if(!$replicationMaster)
  return;
sql("insert into mailings(type_id,receiver_id,link)
     select $typeId,id,$link
     from users
     where (rights & $admin)<>0",
    __FUNCTION__);
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
parent::DataObject($row);
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
      extends MArrayIterator
{

function MailingsExtractIterator()
{
global $mailingClassNames;

$METHOD=get_method($this,'MailingsExtractIterator');
sql('lock tables mailings write,users read,profiling write',
    $METHOD,'lock');
$result=sql('select mailings.id as id,type_id,receiver_id,link,
		    email,email_disabled
	     from mailings
		  left join users
		       on mailings.receiver_id=users.id',
             $METHOD,'select');
$mails=array();
while($row=mysql_fetch_assoc($result))
     {
     $name=$mailingClassNames[$row['type_id']];
     $mails[]=new $name($row);
     }
sql('delete from mailings',
    $METHOD,'delete');
sql('unlock tables',
    $METHOD,'unlock');
parent::MArrayIterator($mails);
}

}
?>
