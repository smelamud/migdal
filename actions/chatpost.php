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
require_once('lib/sql.php');

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
  return EG_OK;
$senderId=$userId<=0 ? $realUserId : $userId;
$result=sql("insert into chat_messages(sender_id,private_id,text)
	     values($senderId,$privateId,'".
	     addslashes(htmlspecialchars($message,ENT_QUOTES))."')",
	    'postMessage');
return EG_OK;
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
while($err==EG_OK & $message!='');
if($err==EG_OK)
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
