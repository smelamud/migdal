<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/topics.php');

function modifyMessage($message)
{
global $userModerator;

if(!$message->isEditable())
  return EM_NO_EDIT;
if($message->body=='')
  return EM_BODY_ABSENT;
if($message->mandatorySubject() && $message->subject='')
  return EM_SUBJECT_ABSENT;
if($message->mandatoryTopic() && $message->topic_id==0)
  return EM_TOPIC_ABSENT;
if($message->topic_id!=0 && !topicExists($message->topic_id))
  return EM_NO_TOPIC;
if($message->personal_id!=0 && !personalExists($message->personal_id))
  return EM_NO_PERSONAL;
if($message->up!=0 && $message->grp!=GRP_FORUMS)
  return EM_FORUM_ANSWER;
if($message->up!=0 && !messageExists($message->up)
  return EM_NO_UP;
if(!$message->store())
  return EM_STORE_SQL;
return EM_OK;
}

dbOpen();
session($sessionid);
$message=getMessageById($editid,$grp);
$message->setup($HTTP_POST_VARS);
$err=modifyMessage($message);
if($err==EM_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  header('Location: /messageedit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('body'),
		    array('bodyid' => $bodyId,
		          'err'    => $err)).'#error');
  }
dbClose();
?>
