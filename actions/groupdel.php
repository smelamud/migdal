<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/groups.php');
require_once('lib/users.php');
require_once('lib/errors.php');

function groupDel($user_id,$group_id)
{
global $userAdminUsers;

if(!$userAdminUsers)
  return EGD_NO_DELETE;
if(!userExists($user_id))
  return EGD_NO_USER;
if(!userExists($group_id))
  return EGD_NO_GROUP;
delUserGroup($user_id,$group_id);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('user_id');
postInteger('group_id');

dbOpen();
session();
$err=groupDel($user_id,$group_id);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
