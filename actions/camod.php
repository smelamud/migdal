<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/complainactions.php');
require_once('lib/complainscripts.php');
require_once('lib/complaintypes.php');
require_once('lib/errors.php');

function modifyComplainAction($action)
{
global $userAdminComplainAnswers;

if(!$userAdminComplainAnswers)
  return ECAM_NO_EDIT;
if(!complainTypeExists($action->type_id))
  return ECAM_NO_TYPE;
if($action->name=='')
  return ECAM_NAME_ABSENT;
if($action->text=='')
  return ECAM_TEXT_ABSENT;
if(!complainScriptExists($action->script_id))
  return ECAM_NO_SCRIPT;
if(!$action->store())
  return ECAM_SQL_STORE;
return ECAM_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$action=getComplainActionById($editid);
$action->setup($HTTP_POST_VARS);
$err=modifyComplainAction($action);
$nameId=tmpTextSave($name);
$textId=tmpTextSave($text);
header('Location: /complainactionedit.php?'.
       ($err==ECAM_OK
        ? makeQuery($HTTP_POST_VARS,
	            array('err','edittag','name','text','type_id'),
		    array('typeid' => $type_id,
		          'nameid' => $nameId,
		          'textid' => $textId))
        : makeQuery($HTTP_POST_VARS,
	            array('name','text','type_id'),
		    array('err'    => $err,
		          'typeid' => $type_id,
		          'nameid' => $nameId,
		          'textid' => $textId))));
dbClose();
?>
