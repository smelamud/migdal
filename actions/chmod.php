<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/permissions.php');
require_once('lib/errors.php');
require_once('lib/postings-info.php');

function doChmod($id,$perms)
{
global $userModerator,$table,$r_user_name,$r_group_name,$r_perm,$perm_string;

if(!$perms->isWritable())
  return ECHM_NO_CHMOD;
if($perms->getUserName()!='')
  {
  $perms->setUserId(getUserIdByLogin(addslashes($perms->getUserName())));
  if($perms->getUserId()<=0)
    return ECHM_NO_USER;
  }
if($perms->getGroupName()!='')
  {
  $perms->setGroupId(getUserIdByLogin(addslashes($perms->getGroupName())));
  if($perms->getGroupId()<=0)
    return ECHM_NO_GROUP;
  }
if($perms->perms<0)
  return ECHM_BAD_PERMS;
setPermsById($perms);
if($r_user_name || $r_group_name || $r_perm)
  setPermsRecursive($table,$id,
		    $r_user_name ? $perms->getUserId() : 0,
		    $r_group_name ? $perms->getGroupId() : 0,
		    $r_perm ? $perm_string : '????????????????');
return ECHM_OK;
}

postInteger('msgid');
postInteger('topic_id');
postString('user_name');
postString('group_name');
postString('perm_string');
postInteger('r_user_name');
postInteger('r_group_name');
postInteger('r_perm');

dbOpen();
session();
if($msgid==0)
  {
  $id=$topic_id;
  $table='topics';
  }
else
  {
  $id=$msgid;
  $table='messages';
  }
$perms=getPermsById($table,$id);
$perms->setup($HTTP_POST_VARS);
$err=doChmod($id,$perms);
if($err==ECHM_OK)
  {
  header('Location: '.remakeURI($okdir,
                                array('err')));
  dropPostingsInfoCache(DPIC_BOTH);
  }
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
                                    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
