<?php
# @(#) $Id$

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
?>
