<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/usertag.php');
require_once('lib/users.php');
require_once('lib/random.php');
require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/modbits.php');
require_once('lib/text-any.php');

class Complain
      extends Entry
{
var $person_info;

function Complain($row)
{
global $tfRegular;

$this->entry=ENT_COMPLAIN;
$this->body_format=$tfRegular;
parent::Entry($row);
$prow=array();
foreach($row as $key => $value)
       if(substr($key,0,7)=='person_')
         $prow[substr($key,7)]=$value;
$this->person_info=new User($prow);
}

function setup($vars)
{
global $tfRegular;

if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->body_format=$tfRegular;
$this->body=$vars['body'];
$this->body_xml=anyToXML($this->body,$this->body_format,MTEXT_SHORT);
$this->subject=$vars['subject'];
$this->subject_sort=convertSort($this->subject);
$this->url=$vars['url'];
}

function isClosed()
{
return ($this->getModbits() & MODC_CLOSED)!=0;
}

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
parent::LimitSelectIterator(
       'Complain',
       'select entries.id as id,subject,body_format,sent,created,modified,
               user_id,group_id,perms,person_id,disabled,modbits,
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

function getComplainAssignee()
{
$mask=USR_MODERATOR|USR_ACCEPTS_COMPLAINS;
$result=sql("select id
             from users
	     where (rights & $mask)=$mask
	     order by id",
	    __FUNCTION__);
$n=mysql_num_rows($result);
return $n<=0 ? 0 : mysql_result($result,random(0,$n-1),0);
}

function storeComplain(&$complain)
{
$jencoded=array('subject' => '','subject_sort' => '','user_id' => 'users',
                'group_id' => 'users','person_id' => 'users','body' => '',
		'body_xml' => '');
$vars=array('entry' => $complain->entry,
            'person_id' => $complain->person_id,
	    'user_id' => $complain->user_id,
	    'group_id' => $complain->group_id,
	    'perms' => $complain->perms,
	    'subject' => $complain->subject,
	    'subject_sort' => $complain->subject_sort,
	    'url' => $complain->url,
	    'body' => $complain->body,
	    'body_xml' => $complain->body_xml,
	    'body_format' => $complain->body_format,
	    'modified' => sqlNow(),
	    'modbits' => $complain->modbits);
if($complain->id)
  {
  $result=sql(sqlUpdate('entries',
			$vars,
			array('id' => $complain->id)),
	      __FUNCTION__,'update');
  journal(sqlUpdate('entries',
		    jencodeVars($vars,$jencoded),
		    array('id' => journalVar('entries',$complain->id))));
  answerUpdate($complain->id);
  }
else
  {
  $vars['sent']=sqlNow();
  $vars['created']=sqlNow();
  $complain->person_id=getComplainAssignee();
  $vars['person_id']=$complain->person_id;
  $result=sql(sqlInsert('entries',
                        $vars),
              __FUNCTION__,'insert');
  $complain->id=sql_insert_id();
  journal(sqlInsert('entries',
                    jencodeVars($vars,$jencoded)),
	  'entries',$complain->id);
  }
return $result;
}

function getRootComplain()
{
global $rootComplainUserName,$rootComplainGroupName,$rootComplainPerms;

return new Complain(
		 array('sender_id' => getUserIdByLogin($rootComplainUserName),
		       'group_id'  => getUserIdByLogin($rootComplainGroupName),
		       'perms'     => $rootComplainPerms));
}

function getComplainById($id,$url='')
{
global $userId,$realUserId,$rootComplainUserName,$rootComplainGroupName,
       $rootComplainPerms;

if(hasCachedValue('obj','entries',$id))
  return getCachedValue('obj','entries',$id);
$result=sql("select entries.id as id,subject,body,body_xml,body_format,url,sent,
                    created,modified,user_id,group_id,perms,person_id,disabled,
		    modbits,users.login as login,users.gender as gender,
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
  {
  $group_id=getUserIdByLogin($rootComplainGroupName);
  $complain=new Complain(array('user_id'  => $userId>0 ? $userId : $realUserId,
                               'group_id' => $group_id,
			       'perms'    => $rootComplainPerms,
		               'url'      => $url));
  }
return $complain;
}

function complainExists($id)
{
$result=sql("select id
	     from entries
	     where id=$id and entry=".ENT_COMPLAIN,
	    __FUNCTION__);
return mysql_num_rows($result)>0;
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
