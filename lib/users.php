<?php
# @(#) $Id$

require_once('lib/usertag.php');
require_once('lib/selectiterator.php');
require_once('lib/months.php');
require_once('lib/utils.php');
require_once('lib/tmptexts.php');
require_once('lib/jdate.php');

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
var $icq;
var $email_disabled;
var $accepts_complains;
var $admin_users;
var $admin_topics;
var $moderator;
var $judge;
var $hidden;
var $online;
var $no_login;
var $has_personal;

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
	     'accepts_complains','admin_users','admin_topics','moderator',
	     'judge','hidden','no_login','has_personal');
}

function getWorldVars()
{
return array('login','name','jewish_name','surname','gender','info','birthday',
             'migdal_student','email','hide_email','icq','email_disabled');
}

function getAdminVars()
{
return array('accepts_complains','admin_users','admin_topics','moderator',
             'judge','hidden','no_login','has_personal');
}

function store()
{
global $userAdminUsers;

$normal=$this->getNormal($userAdminUsers);
if(!$this->id || $this->dup_password!='')
  $normal=array_merge($normal,array('password' => md5($this->password)));
$result=mysql_query($this->id 
                    ? makeUpdate('users',$normal,array('id' => $this->id))
                    : makeInsert('users',$normal));
if(!$this->id)
  $this->id=mysql_insert_id();
return $result;
}

function online()
{
if(!$this->id)
  return false;
return mysql_query('update users set last_online=now() where id='.$this->id);
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
return $this->name.($this->jewish_name!='' ? ' ('.$this->jewish_name.')' : '').
       ' '.$this->surname;
}

function getInfo()
{
return $this->info;
}

function getAge()
{
$bt=explode('-',$this->birthday);
$t=getdate();
$jbt=GregorianToJD($bt[1],$bt[2],$bt[0]);
$jt=GregorianToJD($t['mon'],$t['mday'],$t['year']);
$age=(int)(($jt-$jbt)/365.25);
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
return $this->icq ? '<img src="http://wwp.icq.com/scripts/online.dll?icq='.
                                    $this->icq.'&img=5 width=18 height=18">'
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

function getLastOnline()
{
if(isset($this->online))
  return 'Сейчас';
else
  {
  $t=strtotime($this->last_online);
  return date('j/m/Y в H:i:s',$t);
  }
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

function isModerator()
{
return $this->moderator;
}

function isJudge()
{
return $this->judge;
}

function isHidden()
{
return $this->hidden ? 1 : 0;
}

function isAdminHidden()
{
return $this->hidden>1 ? 1 : 0;
}

function isNoLogin()
{
return $this->no_login;
}

function isHasPersonal()
{
return $this->has_personal;
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
			      sessions.user_id as online
		       from users left join sessions
		                  on users.id=sessions.user_id
				  and sessions.last+interval 1 hour>now()
		       where hidden<$hide
		       order by login");
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
			    moderator,judge,hidden,no_login,has_personal,
			    sessions.user_id as online
		     from users left join sessions
				on users.id=sessions.user_id
				and sessions.last+interval 1 hour>now()
		     where users.id=$id and hidden<$hide")
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
?>
