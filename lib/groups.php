<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/journal.php');

class UserGroup
      extends DataObject
{
var $user_id;
var $group_id;
var $user_name;
var $group_name;

function UserGroup($row)
{
$this->DataObject($row);
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

}

class GroupsIterator
      extends SelectIterator
{

function GroupsIterator()
{
$this->SelectIterator('UserGroup',
                      'select user_id,group_id,users.login as user_name,
		              gusers.login as group_name
		       from groups
		            left join users
			         on user_id=users.id
			    left join users as gusers
			         on group_id=gusers.id
		       order by gusers.login,users.login');
}

}

function isUserInGroup($user_id,$group_id)
{
if($user_id==$group_id)
  return true;
$result=mysql_query("select user_id
		     from groups
		     where user_id=$user_id and group_id=$group_id")
	  or sqlbug('Ошибка SQL при добавлении пользователя в группу');
return mysql_num_rows($result)>0;
}

function addUserGroup($user_id,$group_id)
{
if(isUserInGroup($user_id,$group_id))
  return;
mysql_query("insert into groups(user_id,group_id)
             values($user_id,$group_id)")
  or sqlbug('Ошибка SQL при добавлении пользователя в группу');
journal('insert into groups(user_id,group_id)
         values('.journalVar('users',$user_id).','.
	          journalVar('users',$group_id).')');
}

function delUserGroup($user_id,$group_id)
{
mysql_query("delete from groups
             where user_id=$user_id and group_id=$group_id")
  or sqlbug('Ошибка SQL при удалении пользователя из группы');
journal('delete from groups
         where user_id='.journalVar('users',$user_id).' and
	       group_id='.journalVar('users',$group_id));
}
?>
