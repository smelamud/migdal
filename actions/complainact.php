<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/complains.php');
require_once('lib/complainactions.php');
require_once('lib/complainscripts.php');
require_once('lib/forums.php');
require_once('lib/utils.php');
require_once('lib/opscript.php');
require_once('lib/tmptexts.php');

function executeAction($action,$complain_id)
{
global $userId;

if($action->getId()==0)
  return EECA_NO_ACTION;
$complain=getComplainInfoById($complain_id);
if($complain->getId()==0)
  return EECA_NO_COMPLAIN;
if($complain->getRecipientId()!=$userId)
  return EECA_NO_EXEC;
$forum=new ForumAnswer(array('body' => $action->getText(),
		             'up'   => $complain->getMessageId()));
if(!$forum->store())
  return EECA_SQL_FORUM;
opScript(getScriptBodyById($action->getScriptId()),
	 array('complain_id' => $complain_id,
	       'link'        => $complain->getLink()));
return EECA_OK;
}

settype($actid,'integer');
settype($complain_id,'integer');

dbOpen();
session($sessionid);
$action=getComplainActionById($actid);
$action->setup($HTTP_POST_VARS);
$err=executeAction($action,$complain_id);
if($err==EECA_OK)
  header('Location: '.remakeURI($redir,array('err')));
else
  {
  $textId=tmpTextSave($text);
  header('Location: /complainanswer.php?'.
         makeQuery($HTTP_POST_VARS,
		   array('text'),
		   array('err'    => $err,
		         'textid' => $textId)));
  }
dbClose();
?>
