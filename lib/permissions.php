<?php
# @(#) $Id$

require_once('lib/dataobject.php');

define('PB_USER',0);
define('PB_GROUP',4);
define('PB_OTHER',8);
define('PB_GUEST',12);

define('PERM_READ',1);
define('PERM_WRITE',2);
define('PERM_APPEND',4);
define('PERM_POST',8);

define('PERM_UR',0x0001);
define('PERM_UW',0x0002);
define('PERM_UA',0x0004);
define('PERM_UP',0x0008);
define('PERM_GR',0x0010);
define('PERM_GW',0x0020);
define('PERM_GA',0x0040);
define('PERM_GP',0x0080);
define('PERM_OR',0x0100);
define('PERM_OW',0x0200);
define('PERM_OA',0x0400);
define('PERM_OP',0x0800);
define('PERM_ER',0x1000);
define('PERM_EW',0x2000);
define('PERM_EA',0x4000);
define('PERM_EP',0x8000);

class Permissions
      extends DataObject
{
var $user_id;
var $group_id;
var $user_name;
var $group_name;
var $perms;

function Permissions($row)
{
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
}

function getCorrespondentVars()
{
return array('user_name'/*,'group_name','perms' */);
}

function getUserId()
{
return $this->user_id;
}

function getGroupId()
{
return $this->group_id;
}

function getUserName()
{
return $this->user_name;
}

function getGroupName()
{
return $this->group_name;
}

function getPerms()
{
return $this->perms;
}

}

function getPermsById($table,$id)
{
$user=$table=='messages' ? 'sender_id' : 'user_id';
$result=mysql_query("select $user as user_id,group_id,users.login as user_name,
                            gusers.login as group_name,perms
		     from $table
		          left join users
			       on $table.$user=users.id
		          left join users as gusers
			       on $table.group_id=gusers.id
		     where $table.id=$id")
          or sqlbug('Ошибка SQL при выборке прав');
return mysql_num_rows($result)>0 ? new Permissions(mysql_fetch_assoc($result))
                                 : 0;
}

function setPermsById($table,$id,$perms)
{
$user=$table=='messages' ? 'sender_id' : 'user_id';
mysql_query("update $table
             set $user=".$perms->getUserId().',
                 group_id='.$perms->getGroupId().',
                 perms='.$perms->getPerms()."
	     where id=$id")
  or sqlbug('Ошибка SQL при установке прав');
}
?>
