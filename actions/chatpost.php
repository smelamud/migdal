<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/users.php');

function postMessage($personal,$message)
{
global $userId;

if($userId<=0)
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
$result=mysql_query("insert into chat_messages(sender_id,private_id,text)
                     values($userId,$privateId,'".
		     addslashes(htmlspecialchars($message))."')");
if(!$result)
  return ECHP_SQL_INSERT;
return ECHP_OK;
}

dbOpen();
session($sessionid);
$err=postMessage($personal,$message);
if($err==ECHP_OK)
  header('Location: '.remakeURI($redir,
                                array('err','message'),
				array('personal' => $personal)));
else
  header('Location: '.remakeURI($redir,
                                array(),
				array('err'      => $err,
				      'personal' => $personal,
				      'message'  => $message)));
dbClose();
?>
