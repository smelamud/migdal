<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/complains.php');
require_once('lib/complainactions.php');
require_once('lib/complainscripts.php');
require_once('lib/forums.php');
require_once('lib/uri.php');
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
if($action->getText()!='')
  postForumAnswer($complain->getMessageId(),$action->getText());
$script=getComplainScriptById($action->getScriptId());
$script->exec($complain);
return EECA_OK;
}

postInteger('actid');
postInteger('complain_id');
postString('text');

dbOpen();
session();
$action=getComplainActionById($actid);
$action->setup($HTTP_POST_VARS);
$err=executeAction($action,$complain_id);
if($err==EECA_OK)
  header('Location: '.remakeURI($okdir,array('err')));
else
  {
  $textId=tmpTextSave($text);
  header('Location: '.
         remakeMakeURI($faildir,
		       $HTTP_POST_VARS,
		       array('text','okdir','faildir'),
		       array('err'    => $err,
			     'textid' => $textId)));
  }
dbClose();
?>
