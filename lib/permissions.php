<?php
# @(#) $Id$

require_once('lib/entries.php');
require_once('lib/sql.php');
require_once('lib/perm.php');
require_once('lib/html-cache.php');

// Этот класс используется как объект формы при редактировании permission'ов
// отдельно от всех остальных атрибутов
class Perms
      extends Entry
{

function Perms($row)
{
parent::Entry($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->login=$vars['login'];
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
$this->group_login=$vars['group_login'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
$this->perm_string=$vars['perm_string'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
$this->recursive=$vars['recursive'];
$this->entry=$vars['entry'];
}

}

// Извлечение permission'ов от указанного entry (в виде соответствующего
// наследника Entry)
function getPermsById($id)
{
$result=sql("select entries.id as id,entry,user_id,group_id,
                    users.login as login,gusers.login as group_login,perms
	     from entries
		  left join users
		       on entries.user_id=users.id
		  left join users as gusers
		       on entries.group_id=gusers.id
	     where entries.id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? new Perms(mysql_fetch_assoc($result)) : 0;
}

// Извлечение permission'ов для "корневого" entry указанного класса
// (в виде объекта Entry)
function getRootPerms($class)
{
return new $class(
        array('user_id'  => getUserIdByLogin($GLOBALS["root${class}UserName"]),
              'group_id' => getUserIdByLogin($GLOBALS["root${class}GroupName"]),
	      'perms'    => $GLOBALS["root${class}Perms"]));
}

// Сохранение указанных permission'ов
function setPermsById($perms)
{
sql('update entries
     set user_id='.$perms->getUserId().',
	 group_id='.$perms->getGroupId().',
	 perms='.$perms->getPerms().'
     where id='.$perms->getId(),
    __FUNCTION__);
journal("update entries
         set user_id=".journalVar('users',$perms->getUserId()).',
	     group_id='.journalVar('users',$perms->getGroupId()).',
	     perms='.$perms->getPerms().'
	 where id='.journalVar('entries',$perms->getId()));
incContentVersionsByEntryId($perms->getId());
}

// Сохранение указанных permission'ов рекурсивно от указанного entry. Можно
// не указывать владельца/группу, а в строке прав указывать вопросительные
// знаки.
function setPermsRecursive($id,$user_id,$group_id,$perms,$entry=ENT_NULL)
{
global $journalSeq;

$set=array();
if($user_id!=0)
  $set[]="user_id=$user_id";
if($group_id!=0)
  $set[]="group_id=$group_id";
permStringMask($perms,&$andMask,&$orMask);
$set[]="perms=(perms & $andMask) | $orMask";
$set=join(',',$set);
$entryFilter=$entry!=ENT_NULL ? "entry=$entry" : '1';
sql("update entries
     set $set
     where $entryFilter and ".subtree('entries',$id,true),
    __FUNCTION__);
if($journalSeq!=0)
  journal("perms entries ".journalVar('entries',$id).
		       ' '.journalVar('users',$user_id).
		       ' '.journalVar('users',$group_id)." $perms");
incContentVersionsByEntryId(array('postings','forums','topics'));
}

$permVarietyCache=null;

function permMask($perms,$right)
{
global $permVarietyCache;

if(is_null($permVarietyCache))
  {
  $permVarietyCache=array();
  $result=sql("select distinct perms
	       from entries",
	      __FUNCTION__);
  while($row=mysql_fetch_array($result))
       $permVarietyCache[]=$row[0];
  }
$cases=array();
$all=true;
foreach($permVarietyCache as $perm)
       if(($perm & $right)!=0)
         $cases[]="$perms=$perm";
       else
         $all=false;
if($all)
  return '1';
else
  if(count($cases)==0)
    return '0';
  else
    return '('.join(' or ',$cases).')';
}

// Получение SQL-выражения для проверки наличия указанного права
function permFilter($right,$prefix='',$asGuest=false)
{
global $userId,$userGroups;

$eUserId=!$asGuest ? $userId : 0;
$eUserGroups=!$asGuest ? $userGroups : array();

if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$perms="${prefix}perms";
if($eUserId<=0)
  return permMask($perms,$right<<PB_GUEST);
$groups=array();
foreach($eUserGroups as $g)
       $groups[]="${prefix}group_id=$g";
$groups[]="${prefix}group_id=$eUserId";
return "($eUserId=${prefix}user_id and
	".permMask($perms,$right<<PB_USER).'
	or
	('.join(' or ',$groups).") and
	".permMask($perms,$right<<PB_GROUP)."
	or
	".permMask($perms,$right<<PB_OTHER)."
	or
	".permMask($perms,$right<<PB_GUEST).')';
}
?>
