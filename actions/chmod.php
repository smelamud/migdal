<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/permissions.php');
require_once('lib/errors.php');

function doChmod($msgid,$perms)
{
global $userModerator;

if(!$userModerator)
  return ECHM_NO_CHMOD;
if($perms->getUserName()=='')
  return ECHM_USER_EMPTY;
//if($perms->getGroupName()=='')
//  return ECHM_GROUP_EMPTY;
$perms->setUserId(getUserIdByLogin(addslashes($perms->getUserName())));
if($perms->getUserId()<=0)
  return ECHM_NO_USER;
//$perms->setGroupId(getUserIdByLogin(addslashes($perms->getGroupName())));
//if($perms->getGroupId()<=0)
//  return ECHM_NO_GROUP;
setPermsById($perms);
return ECHM_OK;
}

postInteger('msgid');
postString('user_name');
postString('group_name');

dbOpen();
session();
$perms=getPermsById('messages',$msgid);
$perms->setup($HTTP_POST_VARS);
$err=doChmod($msgid,$perms);
if($err==ECHM_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
                                    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
