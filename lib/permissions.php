<?php
# @(#) $Id$

require_once('lib/entries.php');
require_once('lib/sql.php');

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

define('PERM_NONE',0x0000);
define('PERM_ALL',0xFFFF);

// Проверка, разрешают ли указанные permission'ы указанное действие
function perm($user_id,$group_id,$perms,$right)
{
global $userId,$userGroups;

return $userId==$user_id &&
       ($perms & $right<<PB_USER)!=0
       ||
       ($userId==$group_id || in_array($group_id,$userGroups)) &&
       ($perms & $right<<PB_GROUP)!=0
       ||
       $userId>0 &&
       ($perms & $right<<PB_OTHER)!=0
       ||
       ($perms & $right<<PB_GUEST)!=0;
}

// Извлечение permission'ов от указанного entry (в виде объекта Entry)
function getPermsById($id)
{
$result=sql("select entries.id as id,user_id,group_id,users.login as login,
		    gusers.login as group_login,perms
	     from entries
		  left join users
		       on entries.user_id=users.id
		  left join users as gusers
		       on entries.group_id=gusers.id
	     where entries.id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? new Entry(mysql_fetch_assoc($result)) : 0;
}

// Извлечение permission'ов для "корневого" entry указанного класса
// (в виде объекта Entry)
function getRootPerms($class)
{
return new Entry(
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
}

// Сохранение указанных permission'ов рекурсивно от указанного entry. Можно
// не указывать владельца/группу, а в строке прав указывать вопросительные
// знаки.
function setPermsRecursive($id,$user_id,$group_id,$perms)
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
sql("update entries
     set $set
     where ".subtree('entries',$id,true),
    __FUNCTION__);
if($journalSeq!=0)
  journal("perms entries ".journalVar('entries',$id).
		       ' '.journalVar('users',$user_id).
		       ' '.journalVar('users',$group_id)." $perms");
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
  return 1;
else
  return '('.join(' or ',$cases).')';
}

// Получение SQL-выражения для проверки наличия указанного права
function permFilter($right,$prefix='')
{
global $userId,$userGroups;

if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$perms="${prefix}perms";
if($userId<=0)
  return permMask($perms,$right<<PB_GUEST);
$groups=array();
foreach($userGroups as $g)
       $groups[]="${prefix}group_id=$g";
$groups[]="${prefix}group_id=$userId";
return "($userId=${prefix}user_id and
	".permMask($perms,$right<<PB_USER).'
	or
	('.join(' or ',$groups).") and
	".permMask($perms,$right<<PB_GROUP)."
	or
	".permMask($perms,$right<<PB_OTHER)."
	or
	".permMask($perms,$right<<PB_GUEST).')';
}

// Преобразование строки прав в маску. Вопросительные знаки заменяются
// значениями из $default.
function permString($s,$default='----------------')
{
$tmpl="rwaprwaprwaprwap";
if(strlen($s)!=strlen($tmpl))
  return -1;
$s=strtolower($s);
$perm=0;
$right=1;
for($i=0;$i<strlen($tmpl);$i++,$right*=2)
   {
   $c=$s{$i}=='?' ? $default{$i} : $s{$i};
   if($c==$tmpl{$i})
     $perm|=$right;
   else
     if($c!='-')
       return -1;
   }
return $perm;
}

// Раскладывает строку прав на две маски - AND и OR
function permStringMask($s,&$andMask,&$orMask)
{
$tmpl="rwaprwaprwaprwap";
$andMask=PERM_ALL;
$orMask=PERM_NONE;
if(strlen($s)!=strlen($tmpl))
  return -1;
$s=strtolower($s);
$right=1;
for($i=0;$i<strlen($tmpl);$i++,$right*=2)
   if($s{$i}==$tmpl{$i})
     $orMask|=$right;
   else
     if($s{$i}=='-')
       $andMask&=~$right;
     else
       if($s{$i}!='?')
	 return -1;
}

// Преобразует паску в строку прав
function strPerms($perm,$escape=false)
{
$tmpl="rwaprwaprwaprwap";
$s='';
$right=1;
for($i=0;$i<strlen($tmpl);$i++,$right*=2)
   if(($perm & $right)!=0)
     $s.=$tmpl{$i};
   else
     $s.=$escape ? '&nil;-' : '-';
return $s;
}
?>
