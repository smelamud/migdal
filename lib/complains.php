<?php
# @(#) $Id$

require_once('lib/grps.php');
require_once('lib/messages.php');
require_once('lib/complaintypes.php');
require_once('lib/sendertag.php');

class Complain
      extends Message
{
var $message_id;
var $type_id;
var $link;
var $closed;
var $display;
var $recipient_id;

var $recipient_info;
var $rec_login;
var $rec_gender;
var $rec_email;
var $rec_hide_email;
var $rec_rebe;
var $rec_hidden;

function Complain($row)
{
$this->id=0;
$this->grp=GRP_COMPLAIN;
$this->Message($row);
$this->recipient_info=
       new SenderTag(array('sender_id'     => $this->recipient_id,
			   'login'         => $this->rec_login,
			   'gender'        => $this->rec_gender,
			   'email'         => $this->rec_email,
			   'hide_email'    => $this->rec_hide_email,
			   'rebe'          => $this->rec_rebe,
			   'sender_hidden' => $this->rec_hidden));
}

function getCorrespondentVars()
{
return array('body','subject','type_id','link');
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
return array('message_id','type_id','link','recipient_id');
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

function getAutoAssign()
{
settype($this->recipient_id,'integer');
if($this->recipient_id!=0)
  return $this->recipient_id;
$type=getComplainTypeById($this->type_id);
$assign=$type->getAssign();
if($assign=='')
  return 0;
$result=mysql_query("select id
                     from users
		     where $assign<>0 and accepts_complains<>0")
             or die('Ошибка SQL при выборке пользователя для автопривязки');
if(mysql_num_rows($result)<=0)
  return 0;
srand(time());
return mysql_result($result,rand(0,mysql_num_rows($result)-1),0);
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
  $this->recipient_id=$this->getAutoAssign();
  $complain=$this->getNormalComplain($userJudge);
  $result=mysql_query(makeInsert('complains',$complain));
  }
return $result;
}

function isEditable()
{
global $userId;

return !$this->isClosed() && ($this->sender_id==0 || $this->sender_id==$userId);
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

function setIdent($ident)
{
$type=getComplainTypeByIdent(addslashes($ident!='' ? $ident : 'normal'));
$this->type_id=$type->getId();
}

function getLink()
{
return $this->link;
}

function setLink($link)
{
$this->link=$link;
}

function isClosed()
{
return $this->closed!='';
}

function getClosedView()
{
$t=strtotime($this->closed);
return $this->isClosed() ? date('j/m/Y в H:i:s',$t) : 'Нет';
}

function getDisplay()
{
return $this->display;
}

function getRecipientId()
{
return $this->recipient_id;
}

function isAssigned()
{
return $this->recipient_id!=0;
}

function getRecipientInfo()
{
return $this->recipient_info;
}

function getRecipientLogin()
{
return $this->rec_login;
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
               messages.sent as sent,closed,recipient_id,
               users.login as login,users.gender as gender,
	       users.email as email,users.hide_email as hide_email,
	       users.rebe as rebe,
               recs.login as rec_login,recs.gender as rec_gender,
	       recs.email as rec_email,recs.hide_email as rec_hide_email,
	       recs.rebe as rec_rebe,recs.hidden as rec_hidden,
	       count(answers.up) as answer_count
        from complains
	     left join messages
		  on messages.id=complains.message_id
	     left join users
		  on messages.sender_id=users.id
	     left join users as recs
		  on complains.recipient_id=recs.id
	     left join messages as answers
	          on messages.id=answers.up
	group by messages.id
        order by closed is null desc,sent desc',$limit,$offset,
       'select count(*)
        from complains');
}

}

function getComplainById($id,$ident='normal')
{
$result=mysql_query("select complains.id as id,body,subject,
                            message_id,type_id,link,display
		     from complains
		          left join messages
			       on messages.id=complains.message_id
			  left join complain_types
			       on complains.type_id=complain_types.id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке полной жалобы');
if(mysql_num_rows($result)>0)
  return new Complain(mysql_fetch_assoc($result));
else
  {
  $type=getComplainTypeByIdent($ident);
  return new Complain(array('type_id' => $type->getId(),
                            'display' => $type->getDisplay()));
  }
}

function getComplainInfoById($id)
{
$result=mysql_query("select id,message_id,recipient_id,link
		     from complains
		     where id=$id")
	     or die('Ошибка SQL при выборке информации о жалобе');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					      : array());
}

function getFullComplainById($id,$ident='normal')
{
$result=mysql_query("select complains.id as id,body,subject,sender_id,
                            sent,closed,message_id,type_id,link,display,
                            users.login as login,users.gender as gender,
			    users.email as email,
			    users.hide_email as hide_email,users.rebe as rebe,
			    recipient_id,
			    recs.login as rec_login,recs.gender as rec_gender,
			    recs.email as rec_email,
			    recs.hide_email as rec_hide_email,
			    recs.rebe as rec_rebe,recs.hidden as rec_hidden
		     from complains
		          left join messages
			       on messages.id=complains.message_id
			  left join users
			       on messages.sender_id=users.id
			  left join users as recs
			       on complains.recipient_id=recs.id
			  left join complain_types
			       on complains.type_id=complain_types.id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке жалобы');
if(mysql_num_rows($result)>0)
  return new Complain(mysql_fetch_assoc($result));
else
  {
  $type=getComplainTypeByIdent($ident);
  return new Complain(array('type_id' => $type->getId(),
                            'display' => $type->getDisplay()));
  }
}

function complainExists($id)
{
$result=mysql_query("select id
                     from complains
		     where id=$id")
	     or die('Ошибка SQL при выборке жалобы');
return mysql_num_rows($result)>0;
}
?>
