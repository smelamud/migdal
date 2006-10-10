<?php
# @(#) $Id$

require_once('conf/migdal.conf');

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
require_once('lib/text-any.php');

function executeAction($action,$complain_id)
{
global $userId,$tfForum;

if($action->getId()==0)
  return EECA_NO_ACTION;
$complain=getComplainById($complain_id);
if($complain->getId()==0)
  return EECA_NO_COMPLAIN;
if($complain->getPersonId()!=$userId)
  return EECA_NO_EXEC;
if($action->getText()!='')
  {
  $forum=getForumById(0,$complain->getId());
  $forum->body_format=$tfForum;
  $forum->body=$action->getText();
  $forum->body_xml=anyToXML($forum->body,$forum->body_format,MTEXT_SHORT);
  $forum->up=$complain->getId();
  storeForum($forum);
  }
$script=getComplainScriptById($action->getScriptId());
$script->exec($complain);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('actid');
postInteger('complain_id');
postInteger('script_id');
postString('text');

dbOpen();
session();
$action=getComplainActionById($actid);
$action->setup($Args);
$err=executeAction($action,$complain_id);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  {
  $textId=tmpTextSave($text);
  header('Location: '.
         remakeMakeURI($faildir,
		       $Args,
		       array('text',
		             'okdir',
			     'faildir'),
		       array('err'    => $err,
			     'text_i' => $textId)).'#error');
  }
dbClose();
?>
