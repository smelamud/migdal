<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/complains.php');
require_once('lib/complainactions.php');
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
if($action->getScriptId()!=0)
  {
  $result=mysql_query('select script
		       from complain_scripts
		       where id='.$action->getScriptId());
  if(!$result)
    return EECA_SQL_STATEMENTS;
  opScript(mysql_result($result,0,0),
	   array('complain_id' => $complain_id,
		 'link'        => $complain->getLink()));
  }
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
