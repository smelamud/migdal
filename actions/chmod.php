<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/permissions.php');
require_once('lib/errors.php');

function doChmod($id,$perms,$original)
{
global $userModerator,$userAdminTopics;

$moder=$original->entry==ENT_TOPIC ? $userAdminTopics : $userModerator;
if(!$original->isWritable() || $perms->recursive && !$moder)
  return ECHM_NO_CHMOD;
if($perms->getUserName()!='')
  {
  $perms->setUserId(getUserIdByLogin($perms->getUserName()));
  if($perms->getUserId()<=0)
    return ECHM_NO_USER;
  }
else
  $perms->setUserId(0);
if($perms->getGroupName()!='')
  {
  $perms->setGroupId(getUserIdByLogin($perms->getGroupName()));
  if($perms->getGroupId()<=0)
    return ECHM_NO_GROUP;
  }
else
  $perms->setGroupId(0);
if(!$perms->recursive)
  {
  if($perms->getUserId()<=0)
    return ECHM_USER_EMPTY;
  if($perms->getGroupId()<=0)
    return ECHM_GROUP_EMPTY;
  if($perms->perms<0)
    return ECHM_BAD_PERMS;
  setPermsById($perms);
  }
else
  setPermsRecursive($id,$perms->getUserId(),$perms->getGroupId(),
                    $perms->perm_string!='' ? $perms->perm_string
		                            : '????????????????',
		    $perms->entry);
// FIXME этот скрипт не работает для ответов в форуме. Для них нужно вызывать
// answerUpdate()
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('id');
postInteger('entry');
postString('user_name');
postString('group_name');
postString('perm_string',false);
postInteger('recursive');

dbOpen();
session();
$perms=getPermsById($id);
$original=clone $perms;
$perms->setup($Args);
$err=doChmod($id,$perms,$original);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  header('Location: '.remakeMakeURI($faildir,
                                    $Args,
                                    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
