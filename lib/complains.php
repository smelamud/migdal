<?php
# @(#) $Id$

require_once('lib/grps.php');
require_once('lib/messages.php');

class Complain
      extends Message
{
var $message_id;
var $type_id;
var $closed;

function Complain($row)
{
$this->grp=GRP_COMPLAIN;
$this->Message($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
parent::setup($vars);
if(isset($vars['ident']))
  $this->type_id=getComplainTypeIdByIdent($vars['ident']);
}

function getCorrespondentVars()
{
return array('body','subject','type_id');
}

function getWorldMessageVars()
{
return array('body','subject','grp');
}

function getAdminMessageVars()
{
return array();
}

function getWorldComplainVars()
{
return array('message_id','type_id');
}

function getAdminComplainVars()
{
return array();
}

function getNormalMessage($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldMessageVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminMessageVars()));
return $normal;
}

function getNormalComplain($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldComplainVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminComplainVars()));
return $normal;
}

function store()
{
global $userId,$userJudge;

if($this->id)
  {
  $message=$this->getNormalMessage($userJudge);
  $result=mysql_query(makeUpdate('messages',
                                 $message,
				 array('id' => $this->message_id)));
  $complain=$this->getNormalComplain($userJudge);
  $result=mysql_query(makeUpdate('complains',
                                 $complain,
				 array('id' => $this->id)));
  }
else
  {
  $message=$this->getNormalMessage($userJudge);
  $sent=date('Y-m-d H:i:s',time());
  $message['sender_id']=$userId;
  $message['sent']=$sent;
  $result=mysql_query(makeInsert('messages',$message));
  if(!$result)
    return $result;
  $this->message_id=mysql_insert_id();
  $this->sender_id=$userId;
  $this->sent=$sent;
  $complain=$this->getNormalComplain($userJudge);
  $result=mysql_query(makeInsert('complains',$complain));
  }
return $result;
}

function isEditable()
{
global $userId;

return $this->sender_id==0 || $this->sender_id==$userId;
}

function isModerable()
{
return false;
}

function hasTopic()
{
return false;
}

function hasImage()
{
return false;
}

function getMessageId()
{
return $this->message_id;
}

function getTypeId()
{
return $this->type_id;
}

function isClosed()
{
return $this->closed{0}!='0';
}

function getClosedView()
{
$t=strtotime($this->closed);
return $this->isClosed() ? date('j/m/Y в H:i:s',$t) : 'Нет';
}

}

class ComplainListIterator
      extends LimitSelectIterator
{

function ComplainListIterator($limit=20,$offset=0)
{
$this->LimitSelectIterator(
       'Complain',
       'select complains.id as id,messages.subject as subject,
               messages.sent as sent,closed,
               login,gender,email,hide_email,rebe,
	       count(answers.up) as answer_count
        from complains
	     left join messages
		  on messages.id=complains.message_id
	     left join users
		  on messages.sender_id=users.id
	     left join messages as answers
	          on messages.id=answers.up
	group by messages.id
        order by sent desc',$limit,$offset,
       'select count(*)
        from complains');
}

}

function getComplainById($id,$ident='normal')
{
$result=mysql_query("select complains.id as id,body,subject,
                            message_id,type_id
		     from complains
		          left join messages
			       on messages.id=complains.message_id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке полной жалобы');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                    : array('type_id' => getComplainTypeIdByIdent($ident)));
}

function getFullComplainById($id,$ident='normal')
{
$result=mysql_query("select complains.id as id,body,subject,sender_id,
                            sent,closed,message_id,type_id,
                            login,gender,email,hide_email,rebe
		     from complains
		          left join messages
			       on messages.id=complains.message_id
			  left join users
			       on messages.sender_id=users.id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке жалобы');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                    : array('type_id' => getComplainTypeIdByIdent($ident)));
}

function getComplainTypeIdByIdent($ident)
{
$result=mysql_query("select id
                     from complain_types
		     where ident='$ident'")
	     or die('Ошибка SQL при выборке типа жалобы');
return mysql_result($result,0,0);
}

function complainTypeExists($id)
{
$result=mysql_query("select id
                     from complain_types
		     where id=$id")
	     or die('Ошибка SQL при выборке типа жалобы');
return mysql_num_rows($result)>0;
}
?>
