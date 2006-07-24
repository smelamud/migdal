<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/groups.php');
require_once('lib/errors.php');

function groupAdd($user_name,$group_name)
{
global $userAdminUsers;

if(!$userAdminUsers)
  return EGA_NO_ADD;
if($user_name=='')
  return EGA_USER_EMPTY;
if($group_name=='')
  return EGA_GROUP_EMPTY;
$userId=getUserIdByLogin($user_name);
if($userId<=0)
  return EGA_NO_USER;
$groupId=getUserIdByLogin($group_name);
if($groupId<=0)
  return EGA_NO_GROUP;
addUserGroup($userId,$groupId);
return EG_OK;
}

postString('okdir');
postString('faildir');

postString('user_name');
postString('group_name');

dbOpen();
session();
$err=groupAdd($user_name,$group_name);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('user_name'  => $user_name,
				      'group_name' => $group_name,
				      'err'        => $err)).'#error');
dbClose();
?>
