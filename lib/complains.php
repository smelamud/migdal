<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/messages.php');
require_once('lib/complaintypes.php');
require_once('lib/sendertag.php');
require_once('lib/users.php');

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
$list=Message::getCorrespondentVars();
array_push($list,'type_id','link');
return $list;
}

function getWorldComplainVars()
{
return array('message_id','type_id','link','recipient_id');
}

function getAdminComplainVars()
{
return array();
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
global $userJudge;

$result=Message::store('message_id');
if(!$result)
  return $result;
$complain=$this->getNormalComplain($userJudge);
if($this->id)
  $result=mysql_query(makeUpdate('complains',
                                 $complain,
				 array('id' => $this->id)));
else
  {
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

function getLink()
{
return $this->link;
}

function isClosed()
{
return $this->closed!='';
}

function getClosed()
{
return strtotime($this->closed);
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
	       users.rebe as rebe,users.hidden as sender_hidden,
               recs.login as rec_login,recs.gender as rec_gender,
	       recs.email as rec_email,recs.hide_email as rec_hide_email,
	       recs.rebe as rec_rebe,recs.hidden as rec_hidden,
	       count(forums.up) as answer_count
        from complains
	     left join messages
		  on messages.id=complains.message_id
	     left join users
		  on messages.sender_id=users.id
	     left join users as recs
		  on complains.recipient_id=recs.id
	     left join forums
	          on messages.id=forums.up
	group by messages.id
        order by closed is null desc,sent desc',$limit,$offset,
       'select count(*)
        from complains');
}

}

function getComplainById($id,$ident='normal',$link=0)
{
$result=mysql_query("select complains.id as id,stotext_id,body,subject,
                            message_id,type_id,link,display
		     from complains
		          left join messages
			       on messages.id=complains.message_id
			  left join stotexts
			       on stotexts.id=messages.stotext_id
			  left join complain_types
			       on complains.type_id=complain_types.id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке полной жалобы');
if(mysql_num_rows($result)>0)
  return new Complain(mysql_fetch_assoc($result));
else
  {
  $type=getComplainTypeById($ident);
  return new Complain(array('type_id' => $type->getId(),
                            'display' => $type->getDisplay(),
			    'link'    => $link));
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
$result=mysql_query("select complains.id as id,stotext_id,body,subject,
                            sender_id,sent,closed,message_id,type_id,link,
			    display,users.login as login,
			    users.gender as gender,users.email as email,
			    users.hide_email as hide_email,users.rebe as rebe,
			    users.hidden as sender_hidden,recipient_id,
			    recs.login as rec_login,recs.gender as rec_gender,
			    recs.email as rec_email,
			    recs.hide_email as rec_hide_email,
			    recs.rebe as rec_rebe,recs.hidden as rec_hidden
		     from complains
		          left join messages
			       on messages.id=complains.message_id
			  left join stotexts
			       on stotexts.id=messages.stotext_id
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
  $type=getComplainTypeById($ident);
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

function sendAutomaticComplain($ident,$subject,$body,$link)
{
$type=getComplainTypeById($ident);
$complain=new Complain(array('type_id'   => $type->getId(),
                             'link'      => $link,
                             'subject'   => $subject,
			     'body'      => $body,
			     'sender_id' => getShamesId()));
$complain->store() or die('Ошибка SQL при посылке автоматической жалобы');
}
?>
