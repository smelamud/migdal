<?php
# @(#) $Id$

require_once('lib/grps.php');
require_once('lib/messages.php');

class Complain
      extends Message
{
var $message_id;

function Complain($row)
{
$this->grp=GRP_COMPLAIN;
$this->Message($row);
}

function getCorrespondentVars()
{
return array('body','subject');
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
return array('message_id');
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

function hasTopic()
{
return false;
}

function hasImage()
{
return false;
}

}

function getComplainById($id)
{
$result=mysql_query("select complains.id as id,body,subject,sender_id,
                            message_id
		     from complains
		          left join messages
			       on messages.id=complains.message_id
		     where complains.id=$id")
	     or die('Ошибка SQL при выборке жалобы');
return new Complain(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                              : array());
}

?>
