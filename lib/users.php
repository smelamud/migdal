<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/selectiterator.php');
require_once('lib/calendar.php');
require_once('lib/utils.php');
require_once('lib/tmptexts.php');
require_once('lib/calendar.php');
require_once('lib/random.php');

class User
      extends UserTag
{
var $id;
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

function store()
{
global $userAdminUsers;

$normal=$this->getNormal($userAdminUsers);
if(!$this->id || $this->dup_password!='')
  $normal=array_merge($normal,array('password' => md5($this->password)));
$normal['login_sort']=convertSort($normal['login']);
$result=mysql_query($this->id 
                    ? makeUpdate('users',$normal,array('id' => $this->id))
                    : makeInsert('users',$normal));
if(!$this->id)
  $this->id=mysql_insert_id();
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
return mysql_query("update users
                    set no_login=1,confirm_code='$s',
		        confirm_deadline=now()+interval $regConfirmTimeout day
 	            where id=$this->id");
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

function getInfo()
{
return $this->info;
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
return $this->migdal_student ? 'Да' : 'Нет';
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
      extends SelectIterator
{

function UserListIterator()
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$this->SelectIterator('User',
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
		       order by login_sort");
}

}

function getUserById($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=mysql_query("select distinct users.id as id,login,name,jewish_name,
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
		     group by users.id")
	     or die('Ошибка SQL при выборке данных пользователя');
return new User(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                          : array());
}

function getUserIdByLogin($login)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=mysql_query("select id
                     from users
		     where login='$login' and hidden<$hide")
	     or die('Ошибка SQL при выборке данных пользователя');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getShamesId()
{
$result=mysql_query('select id
                     from users
		     where shames=1')
	     or die('Ошибка SQL при выборке данных шамеса');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function personalExists($id)
{
global $userAdminUsers;

$hide=$userAdminUsers ? 2 : 1;
$result=mysql_query("select id
		     from users
		     where id=$id and hidden<$hide and has_personal<>0")
	     or die('Ошибка SQL при выборке данных пользователя');
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
$result=mysql_query("select count(*),count(confirm_deadline)
                     from users
		     where hidden<$hide")
  	     or die('Ошибка SQL при получении общей информации о пользователях');
return mysql_num_rows($result)>0 ?
       new UsersSummary(mysql_result($result,0,0)-mysql_result($result,0,1),
                        mysql_result($result,0,1)) :
       new UsersSummary(0,0);
}

class ChatUsersIterator
      extends SelectIterator
{

function ChatUsersIterator()
{
global $chatTimeout;

$this->SelectIterator('User',
                      "select id,login,gender,email,hide_email
		       from users
		       where last_chat+interval $chatTimeout minute>now()
		       order by login_sort");
}

}

function getChatUsersCount()
{
global $chatTimeout;

$result=mysql_query("select count(*)
		     from users
		     where last_chat+interval $chatTimeout minute>now()")
             or die('Ошибка SQL при получении количества пользователей в чате');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function updateLastChat()
{
global $userId;

mysql_query("update users
             set last_chat=now()
	     where id=$userId")
     or die('Ошибка SQL при обновлении времени присутствия в чате');
}
?>
