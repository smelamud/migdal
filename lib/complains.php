<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/usertag.php');
require_once('lib/users.php');
require_once('lib/random.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/modbits.php');

class Complain
      extends Entry
{
var $person_info;

function Complain($row)
{
$this->entry=ENT_COMPLAIN;
parent::Entry($row);
$prow=array();
foreach($row as $key => $value)
       if(substr($key,0,7)=='person_')
         $prow[substr($key,7)]=$value;
$this->person_info=new User($prow);
}
/*
function getCorrespondentVars()
{
$list=Message::getCorrespondentVars();
array_push($list,'type_id','link');
return $list;
}

function getWorldComplainVars()
{
return array('message_id','type_id','link','recipient_id','no_auto');
}

function getAdminComplainVars()
{
return array();
}

function getJencodedComplainVars()
{
return array('recipient_id' => 'users','message_id' => 'messages',
             'link' => $this->getLinkTable());
}

function getAutoAssign()
{
settype($this->recipient_id,'integer');
if($this->recipient_id!=0)
  return $this->recipient_id;
$type=newComplain($this->type_id);
$assign=$type->getAssign();
if($assign=='')
  return 0;
$result=sql("select id
	     from users
	     where $assign<>0 and accepts_complains<>0",
	    get_method($this,'getAutoAssign'));
if(mysql_num_rows($result)<=0)
  return 0;
return mysql_result($result,random(0,mysql_num_rows($result)-1),0);
}

function store()
{
global $userJudge;

$result=Message::store('message_id');
if(!$result)
  return $result;
$complain=$this->getNormalComplain($userJudge);
if($this->id)
  {
  $result=sql(makeUpdate('complains',
			 $complain,
			 array('id' => $this->id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('complains',
                     jencodeVars($complain,$this->getJencodedComplainVars()),
		     array('id' => journalVar('complains',$this->id))));
  }
else
  {
  $this->recipient_id=$this->getAutoAssign();
  $complain=$this->getNormalComplain($userJudge);
  $result=sql(makeInsert('complains',$complain),
              get_method($this,'store'),'insert');
  journal(makeInsert('complains',
                     jencodeVars($complain,$this->getJencodedComplainVars())),
	  'complains',sql_insert_id());
  }
return $result;
}
*/
function isPermitted($right)
{
global $userId;

switch($right)
      {
      case PERM_READ:
           return true;
      case PERM_WRITE:
           return $this->getUserId()==$userId || $userModerator;
      case PERM_APPEND:
           return false;
      case PERM_POST:
           return true;
      default:
           return false;
      }
}

function isClosed()
{
return ($this->getModbits() & MODC_CLOSED)!=0;
}
/*
function getDisplay()
{
return $this->display;
}
*/
function getPersonInfo()
{
return $this->person_info;
}

function isAssigned()
{
return $this->person_id!=0;
}

}

class ComplainListIterator
      extends LimitSelectIterator
{

function ComplainListIterator($limit=20,$offset=0)
{
$this->LimitSelectIterator(
       'Complain',
       'select entries.id as id,subject,sent,created,modified,user_id,group_id,
               perms,person_id,disabled,modbits,
	       users.login as login,users.gender as gender,users.email as email,
	       users.hide_email as hide_email,users.hidden as user_hidden,
	       person.login as person_login,person.gender as person_gender,
	       person.email as person_email,
	       person.hide_email as person_hide_email,
	       person.hidden as person_hidden,
	       answers,last_answer,last_answer_id,last_answer_user_id
        from entries
	     left join users
		  on entries.user_id=users.id
	     left join users as person
		  on entries.person_id=person.id
	where entry='.ENT_COMPLAIN.'
        order by (modbits & '.MODC_CLOSED.')<>0 asc,sent desc',
       $limit,$offset,
       'select count(*)
        from entries
	where entry='.ENT_COMPLAIN);
}

}

function getComplainById($id,$url='')
{
global $userId,$realUserId;

if(hasCachedValue('obj','entries',$id))
  return getCachedValue('obj','entries',$id);
$result=sql("select entries.id as id,subject,body,body_xml,url,sent,created,
		    modified,user_id,group_id,perms,person_id,disabled,modbits,
		    users.login as login,users.gender as gender,
		    users.email as email,users.hide_email as hide_email,
		    users.hidden as user_hidden,person.login as person_login,
		    person.gender as person_gender,person.email as person_email,
		    person.hide_email as person_hide_email,
		    person.hidden as person_hidden,answers,last_answer,
		    last_answer_id,last_answer_user_id
	     from entries
		  left join users
		       on entries.user_id=users.id
		  left join users as person
		       on entries.person_id=person.id
		  where entries.id=$id",
	    __FUNCTION__);
if(mysql_num_rows($result)>0)
  {
  $complain=new Complain(mysql_fetch_assoc($result));
  setCachedValue('obj','entries',$id,$complain);
  }
else
  $complain=new Complain(array('user_id' => $userId>0 ? $userId : $realUserId,
		               'url'     => $url));
  # FIXME прописывать perms?
return $complain;
}

// remake
function getComplainInfoById($id)
{
$result=sql("select id,message_id,recipient_id,link
	     from complains
	     where id=$id",
	    'getComplainInfoById');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					      : array());
}

// remake
function getComplainInfoByLink($type_id,$link)
{
$result=sql("select id,type_id,message_id,recipient_id,link,closed
	     from complains
	     where type_id=$type_id and link=$link",
	    'getComplainInfoByLink');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					      : array());
}

function complainExists($id)
{
$result=sql("select id
	     from entries
	     where id=$id and entry=".ENT_COMPLAIN,
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

// remake
function sendAutomaticComplain($type_id,$subject,$body,$link,$no_auto=false)
{
global $rootComplainPerms;

$complain=newComplain($type_id,
                      array('type_id'   => $type_id,
                            'link'      => $link,
                            'subject'   => $subject,
			    'body'      => $body,
			    'sender_id' => getShamesId(),
			    'perms'     => $rootComplainPerms,
			    'no_auto'   => (int)$no_auto));
$complain->store();
}

// remake
function reopenComplain($id,$no_auto=false)
{
$no_auto=(int)$no_auto;
sql("update complains
     set closed=null,no_auto=$no_auto
     where id=$id",
    'reopenComplain');
journal("update complains
         set closed=null,no_auto=$no_auto
	 where id=".journalVar('complains',$id));
}

function assignComplain($id,$person_id)
{
$result=sql("update entries
	     set person_id=$person_id
	     where id=$id",
	    __FUNCTION__);
journal('update entries
         set person_id='.journalVar('users',$person_id).'
	 where id='.journalVar('entries',$id));
}

function setComplainClosedStatus($id,$closed)
{
$expr=$closed ? 'modbits | '.MODC_CLOSED : 'modbits & ~'.MODC_CLOSED;
sql("update entries
     set modbits=$expr
     where id=$id",
    __FUNCTION__);
journal("update entries
         set modbits=$expr
         where id=".journalVar('entries',$id));
}

function openComplain($id)
{
setComplainClosedStatus($id,false);
}

function closeComplain($id)
{
setComplainClosedStatus($id,true);
}
?>
