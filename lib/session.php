<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/tmptexts.php');
require_once('lib/uri.php');
require_once('lib/utils.php');

$userRightNames=array('login','hidden','admin_users','admin_topics',
                      'admin_menu','admin_complain_answers','moderator',
		      'judge');
$userSetNames=array('MsgPortion',
                    'ForumPortion',
                    'ComplainPortion',
	            'ChatPortion',
	            'ChatRefresh');
$userSetDefaults=array('MsgPortion'      => 10,
                       'ForumPortion'    => 10,
                       'ComplainPortion' => 20,
	               'ChatPortion'     => 20,
	               'ChatRefresh'     => 10);
$userSetParams=array('MsgPortion'      => 'mp',
                     'ForumPortion'    => 'fp',
		     'ComplainPortion' => 'cp',
		     'ChatPortion'     => 'chp',
		     'ChatRefresh'     => 'chr');

function getProperName($name)
{
$parts=explode('_',$name);
$proper='';
foreach($parts as $part)
       $proper.=ucfirst($part);
return $proper;
}

function userRights()
{
global $sessionid,$userId,$userRightNames;

settype($sessionid,'integer');

foreach($userRightNames as $name)
       $GLOBALS['user'.getProperName($name)]='';

if(!$sessionid)
  $userId=-1;
else
  {
  $result=mysql_query("select user_id from sessions where sid=$sessionid")
	       or die('������ SQL ��� ������� ������');
  if(mysql_num_rows($result)<=0)
    {
    SetCookie('sessionid',0,0,'/');
    $userId=-1;
    }
  else
    {
    $userId=mysql_result($result,0,0);
    $rights=mysql_query('select '.join(',',$userRightNames).
		       " from users
			 where id=$userId")
	  or die('������ SQL ��� ��������� ���� ������������');
    $info=mysql_fetch_assoc($rights);
    foreach($info as $name => $value)
           $GLOBALS['user'.getProperName($name)]=$value;
    if($GLOBALS['userAdminUsers'])
      $GLOBALS['userHidden']--;
    mysql_query("update sessions set last=null where sid=$sessionid")
	 or die('������ SQL ��� ���������� TIMESTAMP ������');
    SetCookie('sessionid',$sessionid,time()+($sessionTimeout+24)*3600,'/');
    }
  }
}

function userSettings()
{
global $userId,$HTTP_GET_VARS,$HTTP_COOKIE_VARS,
       $userSetNames,$userSetDefaults,$userSetParams;
       
if($userId>0)
  {
  $result=mysql_query("select settings
                       from users
	               where id=$userId")
	       or die('������ SQL ��� ������� ��������� ������������');
  $dbSettings=mysql_result($result,0,0);
  $row=explode(':',$dbSettings);
  }
else
  $row=array();
$cookieSettings=$HTTP_COOKIE_VARS['settings'];
$cookie=explode(':',$cookieSettings);
foreach($userSetNames as $i => $name)
       {
       $row[$name]=$row[$i];
       $cookie[$name]=$cookie[$i];
       }
$update=array();
foreach($userSetNames as $name)
       {
       $par=$HTTP_GET_VARS[$userSetParams[$name]];
       $cook=$cookie[$name];
       $db=$row[$name];
       settype($par,'integer');
       settype($cook,'integer');
       $glob=!empty($par) ? $par :
            (!empty($cook) ? $cook :
            (!empty($db) ? $db : $userSetDefaults[$name]));
       $update[]=$glob;
       $GLOBALS["user$name"]=$glob;
       }
$globs=join(':',$update);
if($userId>0 && $globs!=$dbSettings)
  mysql_query("update users
               set settings='$globs'
	       where id=$userId")
       or die('������ SQL ��� ���������� ��������� ������������');
if($globs!=$cookieSettings)
  SetCookie('settings',$globs,time()+3600*24*366,'/');
}

function redirect()
{
global $REQUEST_URI,$HTTP_GET_VARS,$redir,$redirid;

if(isset($HTTP_GET_VARS['redir']) && $HTTP_GET_VARS['redir']!='')
  {
  $redirid=tmpTextSave($HTTP_GET_VARS['redir']);
  header('Location: '.remakeURI($REQUEST_URI,
                                array('redir'),
		                array('redirid' => $redirid)));
  exit(0);
  }
if($redirid!=0)
  $redir=tmpTextRestore($redirid);
}

function session()
{
userRights();
userSettings();
redirect();
}
?>
