<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/complainactions.php');
require_once('lib/complainscripts.php');
require_once('lib/complaintypes.php');
require_once('lib/errors.php');

function modifyComplainAction($action)
{
global $userAdminComplainAnswers;

if(!$userAdminComplainAnswers)
  return ECAM_NO_EDIT;
if($action->type_id<=COMPL_NONE || $action->type_id>COMPL_MAX)
  return ECAM_NO_TYPE;
if($action->name=='')
  return ECAM_NAME_ABSENT;
if($action->text=='')
  return ECAM_TEXT_ABSENT;
if($action->script_id!=0)
  {
  if(($action->script_id & CSCR_ALL)==0)
    return ECAM_NO_SCRIPT;
  $type=newComplain($action->type_id);
  if(($action->script_id & $type->getScriptMask())==0)
    return ECAM_ILLEGAL_SCRIPT;
  }
$action->store();
return ECAM_OK;
}

postInteger('editid');
postString('name');
postString('text');

dbOpen();
session();
$action=getComplainActionById($editid);
$action->setup($HTTP_POST_VARS);
$err=modifyComplainAction($action);
$nameId=tmpTextSave($name);
$textId=tmpTextSave($text);
header('Location: '.($err==ECAM_OK
       ? remakeMakeURI($okdir,
		       $HTTP_POST_VARS,
		       array('err','edittag','name','text','type_id','okdir',
		             'faildir'),
		       array('typeid' => $type_id,
			     'nameid' => $nameId,
			     'textid' => $textId))
       : remakeMakeURI($faildir,
		       $HTTP_POST_VARS,
		       array('name','text','type_id','okdir','faildir'),
		       array('err'    => $err,
			     'typeid' => $type_id,
			     'nameid' => $nameId,
			     'textid' => $textId))));
dbClose();
?>
