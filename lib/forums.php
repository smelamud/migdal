<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/messages.php');

class ForumAnswer
      extends Message
{
var $message_id;
var $up;

function getCorrespondentVars()
{
$list=parent::getCorrespondentVars();
array_push($list,'up');
return $list;
}

function getWorldForumVars()
{
return array('message_id','up');
}

function getAdminForumVars()
{
return array();
}

function getNormalForum($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldForumVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminForumVars()));
return $normal;
}

function store()
{
global $userModerator;

$result=parent::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalForum($userModerator);
if($this->id)
  $result=mysql_query(makeUpdate('forums',$normal,array('id' => $this->id)));
else
  {
  $result=mysql_query(makeInsert('forums',$normal));
  $this->id=mysql_insert_id();
  }
return $result;
}

function getMessageId()
{
return $this->message_id;
}

function getUpValue()
{
return $this->up;
}

function setUpValue($up)
{
$this->up=$up;
}

}

class ForumAnswerListIterator
      extends LimitSelectIterator
{

function ForumAnswerListIterator($up,$limit=10,$offset=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->LimitSelectIterator(
       'ForumAnswer',
	"select messages.id as id,message_id,body,sent,sender_id,
	        messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join users
		   on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       up=$up
	 order by sent desc",$limit,$offset);
      /* ����� ����� ��������, ���� ����� ������ ����������� ��
	 �������� TODO */
}

}

function getForumAnswerById($id,$up=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select forums.id as id,body,personal_id,sender_id,image_set,up,
                            hidden,disabled
		     from forums
		          left join messages
			       on forums.messages_id=messages.id
		     where id=$id and (hidden<$hide or sender_id=$userId)
		           and (disabled<$hide or sender_id=$userId)")
		    /* ����� ����� ��������, ���� ����� ������ ����������� ��
		       �������� TODO */
	     or die('������ SQL ��� ������� ��������� � ������');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array('up' => $up));
}

function getFullForumAnswerById($id)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query(
	"select messages.id as id,message_id,body,sent,sender_id,
	        messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,images.image_set as image_set,
		images.id as image_id,
		images.small_x<images.large_x or
		images.small_y<images.large_y as has_large_image,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.messages_id=messages.id
	      left join images
		   on messages.image_set=images.image_set
	      left join users
		   on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       forums.id=$id")
      /* ����� ����� ��������, ���� ����� ������ ����������� ��
	 �������� TODO */
 or die('������ SQL ��� ������� ��������� � ������');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}
?>
