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
require_once('lib/alphabet.php');
require_once('lib/sort.php');
require_once('lib/sql.php');

class User
      extends UserTag
{
var $id;
var $login;
var $password;
var $dup_password;
var $name;
var $jewish_name;
var $surname;
var $info;
var $birthday;
var $migdal_student;
var $last_online;
var $last_minutes;
var $icq;
var $email_disabled;
var $accepts_complains;
var $rebe;
var $shames;
var $admin_users;
var $admin_topics;
var $moderator;
var $judge;
var $admin_domain;
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
$this->UserTag($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if(isset($vars['infoid']))
  $this->info=tmpTextRestore($vars['infoid']);
$this->birthday='19'.$vars['birth_year'].'-'.$vars['birth_month']
					.'-'.$vars['birth_day'];
$this->email_disabled=$vars['email_enabled'] ? 0 : 1;
}

function getCorrespondentVars()
{
return array('login','password','dup_password','name','jewish_name','surname',
             'gender','migdal_student','info','email','hide_email','icq',
	     'accepts_complains','admin_users','admin_topics',
	     'admin_complain_answers','moderator','judge','admin_domain','hidden',
	     'no_login','has_personal');
}

function getWorldVars()
{
return array('login','name','jewish_name','surname','gender','info','birthday',
             'migdal_student','email','hide_email','icq','email_disabled');
}

function getAdminVars()
{
return array('accepts_complains','admin_users','admin_topics',
             'admin_complain_answers','moderator','judge','admin_domain','hidden',
	     'no_login','has_personal');
}

function getJencodedVars()
{
return array('login' => '','login_sort' => '','password' => '','name' => '',
             'name_sort' => '','jewish_name' => '','jewish_name_sort' => '',
	     'surname' => '','surname_sort' => '','info' => '','email' => '',
	     'icq' => '','settings' => '');
}

function store()
{
global $userAdminUsers;

$normal=$this->getNormal($userAdminUsers);
if(!$this->id || $this->dup_password!='')
  $normal=array_merge($normal,array('password' => md5($this->password)));
$normal['login_sort']=convertSort($normal['login']);
$normal['name_sort']=convertSort($normal['name']);
$normal['jewish_name_sort']=convertSort($normal['jewish_name']);
$normal['surname_sort']=convertSort($normal['surname']);
if($this->id)
  {
  $result=sql(makeUpdate('users',
                         $normal,
			 array('id' => $this->id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('users',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => journalVar('users',$this->id))));
  }
else
  {
  $result=sql(makeInsert('users',
                         $normal),
	      get_method($this,'store'),'insert');
  $this->id=sql_insert_id();
  journal(makeInsert('users',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'users',$this->id);
  }
return $result;
}

function preconfirm()
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
	     where id=$this->id",
	    get_method($this,'preconfirm'));
journal("update users
         set no_login=1,confirm_code='$s',
	     confirm_deadline=now()+interval $regConfirmTimeout day
 	 where id=".journalVar('users',$this->id));
return $result;
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

function getHTMLInfo()
{
return stotextToHTML(TF_MAIL,$this->getInfo());
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

function isMigdalStudent()
{
return $this->migdal_student;
}

function getMigdalStudent()
{
return $this->migdal_student ? '��' : '���';
}

function getICQ()
{
return $this->icq;
}

function getICQStatusImage()
{
return $this->icq ? '<img src="http://web.icq.com/whitepages/online?icq='.
                                                      $this->icq.'&img=5">'
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
return isset($this->online);
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
return $this->accepts_complains;
}

function isAdminUsers()
{
return $this->admin_users;
}

function isAdminTopics()
{
return $this->admin_topics;
}

function isAdminComplainAnswers()
{
return $this->admin_complain_answers;
}

function isModerator()
{
return $this->moderator;
}

function isJudge()
{
return $this->judge;
}

function isAdminDomain()
{
return $this->admin_domain;
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
      extends LimitSelectIterator
{

function UserListIterator($limit=10,$offset=0,$sort=SORT_LOGIN)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$order=getOrderBy($sort,
                  array(SORT_LOGIN       => 'login_sort',
		        SORT_NAME        => 'name_sort',
			SORT_JEWISH_NAME => 'if(jewish_name_sort<>"",
			                        jewish_name_sort,name_sort)',
			SORT_SURNAME     => 'surname_sort'));
$this->LimitSelectIterator(
       'User',
       "select distinct users.id as id,login,name,jewish_name,
	       surname,gender,birthday,migdal_student,email,
	       hide_email,icq,last_online,
	       max(sessions.user_id) as online,
	       min(floor((unix_timestamp(now())
			 -unix_timestamp(sessions.last))/60))
		    as last_minutes,
	       confirm_deadline is null as confirmed,
	       floor((unix_timestamp(confirm_deadline)
		      -unix_timestamp(now()))/86400)
		    as confirm_days
	from users
	     left join sessions
		  on users.id=sessions.user_id
		     and sessions.last+interval 1 hour>now()
	where hidden<$hide
	group by users.id
	$order",$limit,$offset,
       "select count(*)
	from users
	where hidden<$hide");
}

}

class UserAlphabetIterator
      extends AlphabetIterator
{

function UserAlphabetIterator($sort=SORT_LOGIN)
{
$hide=$userAdminUsers ? 2 : 1;
$fields=array(SORT_LOGIN       => 'login',
	      SORT_NAME        => 'name',
	      SORT_JEWISH_NAME => 'if(jewish_name<>"",jewish_name,name)',
	      SORT_SURNAME     => 'surname');
$field=@$fields[$sort]!='' ? $fields[$sort] : 'login';
$order=getOrderBy($sort,
                  array(SORT_LOGIN       => 'login_sort',
		        SORT_NAME        => 'name_sort',
			SORT_JEWISH_NAME => 'if(jewish_name_sort<>"",
			                        jewish_name_sort,name_sort)',
			SORT_SURNAME     => 'surname_sort'));
$this->AlphabetIterator("select left($field,1) as letter,count(*) as count
                         from users
			 where hidden<$hide
			 group by users.id
			 $order");
}

}

function getUserById($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select distinct users.id as id,login,name,jewish_name,
		    surname,gender,info,birthday,migdal_student,
		    last_online,email,hide_email,icq,email_disabled,
		    accepts_complains,admin_users,admin_topics,
		    admin_complain_answers,moderator,judge,admin_domain,
		    hidden,no_login,has_personal,
		    max(sessions.user_id) as online,
		    min(floor((unix_timestamp(now())
			      -unix_timestamp(sessions.last))/60))
			 as last_minutes,
		    confirm_code,
		    confirm_deadline is null as confirmed,
		    floor((unix_timestamp(confirm_deadline)
			   -unix_timestamp(now()))/86400)
			 as confirm_days
	     from users
		  left join sessions
		       on users.id=sessions.user_id
			  and sessions.last+interval 1 hour>now()
	     where users.id=$id and hidden<$hide
	     group by users.id",
	    'getUserById');
return new User(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                          : array());
}

function getUserLoginById($id)
{
// Hidden users' logins must be returned, because system users must be
// identified

$result=sql("select login
	     from users
	     where id=$id",
	    'getUserLoginById');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}


function getUserGenderById($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select gender
	     from users
	     where id=$id and hidden<$hide",
	    'getUserGenderById');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 'mine';
}

function getUserIdByLogin($login)
{
$result=sql("select id
	     from users
	     where login='$login'",
	    'getUserIdByLogin');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getUserIdByLoginPassword($login,$password)
{
$result=sql("select id
	     from users
	     where login='$login' and password='".md5($password)."'
		   and no_login=0",
	    'getUserIdByLoginPassword');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getShamesId()
{
$result=sql('select id
	     from users
	     where shames=1',
	    'getShamesId');
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
	    'getGuestId','locate_free');
if(mysql_num_rows($result)>0)
  return mysql_result($result,0,0);
$result=sql("select login
	     from users
	     where guest<>0
	     order by login_sort desc
	     limit 1",
	    'getGuestId','find_last');
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
    'updateLastOnline');
}

function getSettingsByUserId($userId)
{
$result=sql("select settings
	     from users
	     where id=$userId",
	    'getSettingsByUserId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}

function updateUserSettings($userId,$settings)
{
sql("update users
     set settings='$settings'
     where id=$userId",
    'updateUserSettings');
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
	    'personalExists');
return mysql_num_rows($result)>0;
}

function userExists($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=sql("select id
	     from users
	     where id=$id and hidden<$hide",
	    'userExists');
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
	    'getUsersSummary');
return mysql_num_rows($result)>0 ?
       new UsersSummary(mysql_result($result,0,0)-mysql_result($result,0,1),
                        mysql_result($result,0,1)) :
       new UsersSummary(0,0);
}
?>
