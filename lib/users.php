<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/limitselect.php');
require_once('lib/calendar.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/calendar.php');
require_once('lib/random.php');
require_once('lib/text.php');
require_once('lib/text-wiki.php');
require_once('lib/alphabet.php');
require_once('lib/sort.php');
require_once('lib/sql.php');
require_once('lib/charsets.php');
require_once('lib/mtext-html.php');
require_once('lib/ctypes.php');

define('USR_MIGDAL_STUDENT',0x0001);
define('USR_ACCEPTS_COMPLAINS',0x0002);
define('USR_REBE',0x0004);
define('USR_ADMIN_USERS',0x0008);
define('USR_ADMIN_TOPICS',0x0010);
define('USR_ADMIN_COMPLAIN_ANSWERS',0x0020);
define('USR_MODERATOR',0x0040);
define('USR_JUDGE',0x0080);
define('USR_ADMIN_DOMAIN',0x0100);

define('USR_USER',USR_MIGDAL_STUDENT);
define('USR_ADMIN',USR_ACCEPTS_COMPLAINS|USR_REBE|USR_ADMIN_USERS
                   |USR_ADMIN_TOPICS|USR_ADMIN_COMPLAIN_ANSWERS|USR_MODERATOR
		   |USR_JUDGE|USR_ADMIN_DOMAIN);

class User
      extends UserTag
{
var $id;
var $login_sort;
var $password;
var $dup_password;
var $name;
var $name_sort;
var $jewish_name;
var $jewish_name_sort;
var $surname;
var $surname_sort;
var $info;
var $info_xml;
var $birthday;
var $rights;
var $last_online;
var $last_minutes;
var $icq;
var $email_disabled;
var $accepts_complains;
var $shames;
var $hidden;
var $online;
var $no_login;
var $has_personal;
var $confirm_code;
var $confirmed;
var $confirm_days;
var $last_message;

function User($row)
{
parent::UserTag($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->login=$vars['new_login'];
$this->login_sort=convertSort($this->login);
$this->password=$vars['new_password'];
$this->dup_password=$vars['dup_password'];
$this->name=$vars['name'];
$this->name_sort=convertSort($this->name);
$this->jewish_name=$vars['jewish_name'];
$this->jewish_name_sort=convertSort($this->jewish_name);
$this->surname=$vars['surname'];
$this->surname_sort=convertSort($this->surname);
$this->gender=$vars['gender'];
$this->rights=disjunct($vars['rights']);
$this->info=$vars['info'];
$this->info_xml=wikiToXML($this->info,TF_MAIL,MTEXT_SHORT);
$this->email=$vars['email'];
$this->hide_email=$vars['hide_email'];
$this->icq=$vars['icq'];
$this->hidden=$vars['hidden'];
$this->no_login=$vars['no_login'];
$this->has_personal=$vars['has_personal'];
$this->birthday=sprintf('19%02u-%02u-%02u',$vars['birth_year'],
                        $vars['birth_month'],$vars['birth_day']);
$this->email_disabled=$vars['email_enabled'] ? 0 : 1;
}

function isEditable()
{
global $userId,$userAdminUsers;

return $this->id==0 || $this->id==$userId || $userAdminUsers;
}

function getId()
{
return $this->id;
}

// Used by UserTag::getUserFolder()
function getUserId()
{
return $this->getId();
}

function getFolder()
{
return $this->getUserFolder();
}

function getName()
{
return $this->name;
}

function getJewishName()
{
return $this->jewish_name;
}

function getSurname()
{
return $this->surname;
}

function getFullName()
{
if($this->jewish_name!='')
  return "$this->jewish_name ($this->name) $this->surname";
else
  return "$this->name $this->surname";
}

function getFullNameCivil()
{
if($this->jewish_name!='')
  return "$this->name ($this->jewish_name) $this->surname";
else
  return "$this->name $this->surname";
}

function getFullNameSurname()
{
if($this->jewish_name!='')
  return "$this->surname $this->jewish_name ($this->name)";
else
  return "$this->surname $this->name";
}

function getInfo()
{
return $this->info;
}

function getInfoXML()
{
return $this->info_xml;
}

function getInfoHTML()
{
return mtextToHTML($this->getInfoXML(),MTEXT_SHORT);
}

function getAge()
{
$bt=explode('-',$this->birthday);
$t=getdate();
$age=getCalendarAge($bt[1],$bt[2],$bt[0],$t['mon'],$t['mday'],$t['year']);
return $age<100 ? $age : '-';
}

function getBirthday()
{
$bt=explode('-',$this->birthday);
return $bt[2].' '.getRussianMonth((int)$bt[1]).' '.$bt[0];
}

function getJewishBirthday()
{
$bt=explode('-',$this->birthday);
return getJewishFromDate($bt[1],$bt[2],$bt[0]);
}

function getDayOfBirth()
{
$bt=explode('-',$this->birthday);
return $bt[2] ? $bt[2] : 1;
}

function getMonthOfBirth()
{
$bt=explode('-',$this->birthday);
return $bt[1] ? $bt[1] : 1;
}

function isMonthOfBirth($month)
{
return $this->getMonthOfBirth()==$month;
}

function getYearOfBirth()
{
$bt=explode('-',$this->birthday);
$c=substr($bt[0],2);
return $c ? $c : '00';
}

function getRights()
{
return $this->rights;
}

function hasRight($right)
{
return ($this->rights & $right)!=0;
}

function isMigdalStudent()
{
return $this->hasRight(USR_MIGDAL_STUDENT);
}

function getICQ()
{
return $this->icq;
}

function getICQStatusImage()
{
$icqH=htmlspecialchars($this->icq,ENT_QUOTES);
return $icqH
       ? "<img src=\"http://web.icq.com/whitepages/online?icq=$icqH&img=5\">"
       : '';
}

function isEmailDisabled()
{
return $this->email_disabled;
}

function isEmailEnabled()
{
return $this->email_disabled==0;
}

function isOnline()
{
return $this->online!=0;
}

function isTooOld()
{
return time()-$this->getLastOnline()>365*24*60*60;
}

function getLastOnline()
{
return strtotime($this->last_online);
}

function getLastMinutes()
{
return $this->last_minutes;
}

function isAcceptsComplains()
{
return $this->hasRight(USR_ACCEPTS_COMPLAINS);
}

function isAdminUsers()
{
return $this->hasRight(USR_ADMIN_USERS);
}

function isAdminTopics()
{
return $this->hasRight(USR_ADMIN_TOPICS);
}

function isAdminComplainAnswers()
{
return $this->hasRight(USR_ADMIN_COMPLAIN_ANSWERS);
}

function isModerator()
{
return $this->hasRight(USR_MODERATOR);
}

function isJudge()
{
return $this->hasRight(USR_JUDGE);
}

function isAdminDomain()
{
return $this->hasRight(USR_ADMIN_DOMAIN);
}

function isHidden()
{
return $this->hidden ? 1 : 0;
}

function isAdminHidden()
{
return $this->hidden>1 ? 1 : 0;
}

function isVisible()
{
global $userAdminUsers;

return !$this->isHidden() || ($userAdminUsers && !$this->isAdminHidden());
}

function isNoLogin()
{
return $this->no_login;
}

function isHasPersonal()
{
return $this->has_personal;
}

function getConfirmCode()
{
return $this->confirm_code;
}

function isConfirmed()
{
return $this->confirmed;
}

function getConfirmDays()
{
return $this->confirm_days;
}

function getLastMessage()
{
return !empty($this->last_message) ? strtotime($this->last_message) : 0;
}

}

class UserListIterator
      extends SelectIterator
{

function UserListIterator($prefix,$sort=SORT_LOGIN)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$sortFields=array(SORT_LOGIN       => 'login_sort',
		  SORT_NAME        => 'name_sort',
		  SORT_JEWISH_NAME => 'if(jewish_name_sort<>"",
			                  jewish_name_sort,name_sort)',
		  SORT_SURNAME     => 'surname_sort');
if($prefix!='')
  {
  $sortField=@$sortFields[$sort]!='' ? $sortFields[$sort] : 'login_sort';
  $fieldFilter="and $sortField like '".convertSort($prefix)."%'";
  }
else
  $fieldFilter='';
$order=getOrderBy($sort,
                  array(SORT_LOGIN       => 'login_sort',
		        SORT_NAME        => 'name_sort,surname_sort',
		        SORT_JEWISH_NAME => 'if(jewish_name_sort<>"",
			                        jewish_name_sort,name_sort),
					     surname_sort',
		        SORT_SURNAME     => 'surname_sort,name_sort'));
parent::SelectIterator(
	'User',
	"select id,login,name,jewish_name,surname,gender,birthday,rights,email,
	        hide_email,icq,last_online,
		if(last_online+interval 1 hour>now(),1,0) as online,
		floor((unix_timestamp(now())
		       -unix_timestamp(last_online))/60) as last_minutes,
		confirm_deadline is null as confirmed,
		floor((unix_timestamp(confirm_deadline)
		       -unix_timestamp(now()))/86400) as confirm_days
	 from users
	 where hidden<$hide $fieldFilter
	 group by users.id
	 $order");
}

}

class UserAlphabetIterator
      extends AlphabetIterator
{

function UserAlphabetIterator($limit=0,$sort=SORT_LOGIN)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$fields=array(SORT_LOGIN       => 'login',
	      SORT_NAME        => 'name',
	      SORT_JEWISH_NAME => 'if(jewish_name<>"",jewish_name,name)',
	      SORT_SURNAME     => 'surname');
$field=@$fields[$sort]!='' ? $fields[$sort] : 'login';
$sortFields=array(SORT_LOGIN       => 'login_sort',
		  SORT_NAME        => 'name_sort',
		  SORT_JEWISH_NAME => 'if(jewish_name_sort<>"",
			                  jewish_name_sort,name_sort)',
		  SORT_SURNAME     => 'surname_sort');
$sortField=@$sortFields[$sort]!='' ? $sortFields[$sort] : 'login_sort';
$order=getOrderBy($sort,$sortFields);
parent::AlphabetIterator("select left($field,@len@) as letter,1 as count
                          from users
			  where hidden<$hide and guest=0
			        and $sortField like '@prefix@%'
			  $order",$limit,true);
}

}

function getUserById($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select id,login,name,jewish_name,surname,gender,info,info_xml,
                    birthday,rights,last_online,email,hide_email,icq,
		    email_disabled,hidden,no_login,has_personal,
		    if(last_online+interval 1 hour>now(),1,0) as online,
		    floor((unix_timestamp(now())
			   -unix_timestamp(last_online))/60) as last_minutes,
		    confirm_code,
		    confirm_deadline is null as confirmed,
		    floor((unix_timestamp(confirm_deadline)
			   -unix_timestamp(now()))/86400) as confirm_days
	     from users
	     where users.id=$id and hidden<$hide
	     group by users.id",
	    __FUNCTION__);
return new User(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                          : array());
}

function storeUser(&$user)
{
global $userAdminUsers;

$jencoded=array('login' => '','login_sort' => '','password' => '','name' => '',
                'name_sort' => '','jewish_name' => '','jewish_name_sort' => '',
 	        'surname' => '','surname_sort' => '','info' => '',
		'info_xml' => '','email' => '','icq' => '','settings' => '');
// Здесь допускается установка админских прав не админом! Проверка должна
// производиться раньше.
$vars=array('login' => $user->login,
	    'login_sort' => $user->login_sort,
            'name' => $user->name,
            'name_sort' => $user->name_sort,
	    'jewish_name' => $user->jewish_name,
            'jewish_name_sort' => $user->jewish_name_sort,
	    'surname' => $user->surname,
            'surname_sort' => $user->surname_sort,
	    'gender' => $user->gender,
	    'info' => $user->info,
	    'info_xml' => $user->info_xml,
	    'birthday' => $user->birthday,
            'rights' => $user->rights,
	    'email' => $user->email,
	    'hide_email' => $user->hide_email,
	    'email_disabled' => $user->email_disabled,
	    'icq' => $user->icq);
if($userAdminUsers)
  $vars=array_merge($vars,
		    array('hidden' => $user->hidden,
			  'no_login' => $user->no_login,
			  'has_personal' => $user->has_personal));
if(!$user->id || $user->dup_password!='')
  $vars=array_merge($vars,
                    array('password' => md5($user->password)));
if($user->id)
  {
  $result=sql(makeUpdate('users',
                         $vars,
			 array('id' => $user->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('users',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('users',$user->id))));
  }
else
  {
  $result=sql(makeInsert('users',
                         $vars),
	      __FUNCTION__,'insert');
  $user->id=sql_insert_id();
  journal(makeInsert('users',
                     jencodeVars($vars,$jencoded)),
	  'users',$user->id);
  }
return $result;
}

function preconfirmUser($userId)
{
global $regConfirmTimeout;

$s='';
for($i=0;$i<20;$i++)
   {
   $s.=chr(random(ord('A'),ord('Z')));
   }
$result=sql("update users
	     set no_login=1,confirm_code='$s',
		 confirm_deadline=now()+interval $regConfirmTimeout day
	     where id=$userId",
	    __FUNCTION__);
journal("update users
         set no_login=1,confirm_code='$s',
	     confirm_deadline=now()+interval $regConfirmTimeout day
 	 where id=".journalVar('users',$userId));
return $result;
}

function getUserLoginById($id)
{
// Hidden users' logins must be returned, because system users must be
// identified

$result=sql("select login
	     from users
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}


function getUserGenderById($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select gender
	     from users
	     where id=$id and hidden<$hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 'mine';
}

function getUserIdByLogin($login)
{
$loginS=addslashes($login);
$result=sql("select id
	     from users
	     where login='$loginS'",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getUserIdByLoginPassword($login,$password)
{
$loginS=addslashes($login);
$passwordMD5=md5($password);
$result=sql("select id
	     from users
	     where login='$login' and password='$passwordMD5'
		   and no_login=0",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getShamesId()
{
$result=sql('select id
	     from users
	     where shames=1',
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getGuestId()
{
global $allowGuests,$sessionTimeout,$guestLogin;

if(!$allowGuests)
  return 0;
$result=sql("select id
	     from users
	     where guest<>0 and
		   last_online+interval $sessionTimeout hour<now()
	     order by login_sort
	     limit 1",
	    __FUNCTION__,'locate_free');
if(mysql_num_rows($result)>0)
  return mysql_result($result,0,0);
$result=sql("select login
	     from users
	     where guest<>0
	     order by login_sort desc
	     limit 1",
	    __FUNCTION__,'find_last');
if(mysql_num_rows($result)>0)
  {
  $login=mysql_result($result,0,0);
  $lt=$login{strlen($login)-1};
  switch($lt)
        {
	case '9':
	     $lt='A';
	     break;
	case 'Z':
	     $lt='a';
	     break;
	case 'z':
	     return 0;
	default:
	     $lt=chr(ord($lt)+1);
	}
  $login{strlen($login)-1}=$lt;
  }
else
  $login="$guestLogin-0";
$login_sort=convertSort($login);
sql("insert into users(login,login_sort,email_disabled,guest,hidden,no_login)
     values('$login','$login_sort',1,1,2,1)",
    'getGuestId','create');
$id=sql_insert_id();
journal("insert into users(login,login_sort,email_disabled,guest,hidden,
                           no_login)
         values('".jencode($login)."','".jencode($login_sort)."',1,1,2,1)",
	 'users',$id);
return $id;
}

function updateLastOnline($userId)
{
sql("update users
     set last_online=now()
     where id=$userId",
    __FUNCTION__);
}

function getSettingsByUserId($userId)
{
$result=sql("select settings
	     from users
	     where id=$userId",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}

function updateUserSettings($userId,$settings)
{
sql("update users
     set settings='$settings'
     where id=$userId",
    __FUNCTION__);
journal("update users
	 set settings='".jencode($settings)."'
	 where id=".journalVar('users',$userId));
}

function personalExists($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select id
	     from users
	     where id=$id and hidden<$hide and has_personal<>0",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function userExists($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select id
	     from users
	     where id=$id and hidden<$hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function userLoginExists($login,$excludeId=0)
{
$loginS=addslashes($login);
$filter=$excludeId!=0 ? "and id<>$excludeId" : '';
$result=sql("select id
             from users
	     where login='$loginS' $filter",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

class UsersSummary
{
var $total;
var $waiting;

function UsersSummary($total,$waiting)
{
$this->total=$total;
$this->waiting=$waiting;
}

function getTotal()
{
return $this->total;
}

function getWaiting()
{
return $this->waiting;
}

}

function getUsersSummary()
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select count(*),count(confirm_deadline)
	     from users
	     where hidden<$hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ?
       new UsersSummary(mysql_result($result,0,0)-mysql_result($result,0,1),
                        mysql_result($result,0,1)) :
       new UsersSummary(0,0);
}
?>
