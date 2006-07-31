<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/complainactions.php');
require_once('lib/complainscripts.php');
require_once('lib/errors.php');

function modifyComplainAction($action)
{
global $userAdminComplainAnswers;

if(!$userAdminComplainAnswers)
  return ECAM_NO_EDIT;
if($action->name=='')
  return ECAM_NAME_ABSENT;
if($action->text=='')
  return ECAM_TEXT_ABSENT;
if($action->script_id!=0 && ($action->script_id & CSCR_ALL)==0)
  return ECAM_NO_SCRIPT;
storeComplainAction($action);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('editid');
postString('name');
postString('text');
postInteger('script_id');

dbOpen();
session();
$action=getComplainActionById($editid);
$action->setup($Args);
$err=modifyComplainAction($action);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
			        array('err',
				      'edittag',
				      'name',
				      'text',
				      'name_i',
				      'text_i',
				      'script_id')));
else
  {
  $nameId=tmpTextSave($name);
  $textId=tmpTextSave($text);
  header('Location: '.remakeURI($faildir,
			        array('name','text'),
				array('err'       => $err,
				      'edittag'   => 1,
				      'name_i'    => $nameId,
				      'text_i'    => $textId,
				      'script_id' => $script_id)));
  }
dbClose();
?>
