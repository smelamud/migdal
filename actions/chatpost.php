<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/post.php');
require_once('lib/text.php');

function postMessage($personal,$message)
{
global $userId,$realUserId,$allowGuestChat;

if($userId<=0 && ($realUserId<=0 || !$allowGuestChat))
  return ECHP_NO_ADD;
$privateId=0;
if($personal!='')
  {
  $privateId=getUserIdByLogin($personal);
  if($privateId==0)
    return ECHP_NO_PERSON;
  }
if($message=='')
  return ECHP_OK;
$senderId=$userId<=0 ? $realUserId : $userId;
$result=mysql_query("insert into chat_messages(sender_id,private_id,text)
                     values($senderId,$privateId,'".
		     addslashes(htmlspecialchars($message,ENT_QUOTES))."')");
if(!$result)
  return ECHP_SQL_INSERT;
return ECHP_OK;
}

postString('message');
postString('personal');

dbOpen();
session();
do
  {
  $s=shorten($message,200,100,40);
  $err=postMessage($personal,$s);
  $message=substr($message,strlen($s));
  if($message!='')
    sleep(1);
  }
while($err==ECHP_OK & $message!='');
if($err==ECHP_OK)
  header('Location: '.remakeURI($okdir,
                                array('err','message'),
				array('personal' => $personal)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err'      => $err,
				      'personal' => $personal,
				      'message'  => $message)));
dbClose();
?>
