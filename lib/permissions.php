<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/topics.php');
require_once('lib/messages.php');
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

$permModels=array('topics'   => array('Topic','user_id'),
                  'messages' => array('Message','sender_id'));
$permClassTables=array('topic'   => 'topics',
                       'message' => 'messages');
		       
function getPermsById($table,$id)
{
global $permModels;

list($class,$user)=$permModels[$table];
$result=sql("select $table.id as id,$user,group_id,users.login as login,
		    gusers.login as group_login,perms
	     from $table
		  left join users
		       on $table.$user=users.id
		  left join users as gusers
		       on $table.group_id=gusers.id
	     where $table.id=$id",
	    'getPermsById');
return mysql_num_rows($result)>0 ? new $class(mysql_fetch_assoc($result)) : 0;
}

function getRootPerms($table)
{
global $permModels;

list($class,$user)=$permModels[$table];
return new $class(
        array($user      => getUserIdByLogin($GLOBALS["root${class}UserName"]),
              'group_id' => getUserIdByLogin($GLOBALS["root${class}GroupName"]),
	      'perms'    => $GLOBALS["root${class}Perms"]));
}

function setPermsById($perms)
{
global $permModels,$permClassTables;

$table=$permClassTables[strtolower(get_class($perms))];
list($class,$user)=$permModels[$table];
sql("update $table
     set $user=".$perms->getUserId().',
	 group_id='.$perms->getGroupId().',
	 perms='.$perms->getPerms().'
     where id='.$perms->getId(),
    'setPermsById');
journal("update $table
         set $user=".journalVar('users',$perms->getUserId()).',
	     group_id='.journalVar('users',$perms->getGroupId()).',
	     perms='.$perms->getPerms().'
	 where id='.journalVar($table,$perms->getId()));
}

function setPermsRecursive($table,$id,$user_id,$group_id,$perms)
{
global $permModels,$journalSeq;

list($class,$user)=$permModels[$table];
$set=array();
if($user_id!=0)
  $set[]="$user=$user_id";
if($group_id!=0)
  $set[]="group_id=$group_id";
permStringMask($perms,&$andMask,&$orMask);
$set[]="perms=(perms & $andMask) | $orMask";
$set=join(',',$set);
sql("update $table
     set $set
     where ".subtree($table,$id,true),
    'setPermsRecursive');
if($journalSeq!=0)
  journal("perms $table ".journalVar($table,$id).
		      ' '.journalVar('users',$user_id).
		      ' '.journalVar('users',$group_id)." $perms");
}

$permVarietyCache=array();

function permMask($table,$perms,$right)
{
global $permVarietyCache;

if(!is_array($permVarietyCache[$table]))
  {
  $permVarietyCache[$table]=array();
  $result=sql("select distinct perms
	       from $table",
	      'permMask');
  while($row=mysql_fetch_array($result))
       $permVarietyCache[$table][]=$row[0];
  }
$cases=array();
$all=true;
foreach($permVarietyCache[$table] as $perm)
       if(($perm & $right)!=0)
         $cases[]="$perms=$perm";
       else
         $all=false;
if($all)
  return 1;
else
  return '('.join(' or ',$cases).')';
}

function permFilter($table,$right,$user_id='user_id',$prefix='')
{
global $userId,$userGroups;

if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$perms="${prefix}perms";
if($userId<=0)
  return permMask($table,$perms,$right<<PB_GUEST);
$groups=array();
foreach($userGroups as $g)
       $groups[]="${prefix}group_id=$g";
$groups[]="${prefix}group_id=$userId";
return "($userId=${prefix}$user_id and
	".permMask($table,$perms,$right<<PB_USER).'
	or
	('.join(' or ',$groups).") and
	".permMask($table,$perms,$right<<PB_GROUP)."
	or
	".permMask($table,$perms,$right<<PB_OTHER)."
	or
	".permMask($table,$perms,$right<<PB_GUEST).')';
}

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
