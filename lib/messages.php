<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');

class Message
      extends DataObject
{
var $id;
var $body;
var $subject;
var $topic_id;
var $private_id;
var $sender_id;
var $grp;
var $up;
var $hidden;
var $sent;

function Message($row)
{
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if(isset($vars['bodyid']))
  $this->body=tmpTextRestore($vars['bodyid']);
}

function getCorrespondentVars()
{
return array('body','subject','topic_id','grp','up','hidden');
}

function getWorldVars()
{
return array('body','subject','topic_id','grp','up','hidden');
}

function getAdminVars()
{
return array();
}

function getWorldVarValues()
{
$vals=array();
foreach($this->getWorldVars() as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function getAdminVarValues()
{
$vals=array();
foreach($this->getAdminVars() as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function getId()
{
return $this->id;
}

function getSubject()
{
return $this->subject;
}

function getBody()
{
return $this->body;
}

function isHidden()
{
return $this->hidden;
}

}

class Forum
      extends Message
{

function Forum($row)
{
$this->grp=GRP_FORUMS;
$this->Message($row);
}

}

class News
      extends Message
{

function News($row)
{
$this->grp=GRP_NEWS;
$this->Message($row);
}

}

class Gallery
      extends Message
{

function Gallery($row)
{
$this->grp=GRP_GALLERY;
$this->Message($row);
}

}

class Poll
      extends Message
{

function Poll($row)
{
$this->grp=GRP_POLL;
$this->Message($row);
}

}

function newMessage($row)
{
$name=getGrpClassName($row['grp']);
return new $name($row);
}

function newGrpMessage($grp)
{
$name=getGrpClassName($grp);
return new $name($row);
}

function getMessageById($id,$grp=0)
{
global $userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id,body,subject,topic_id,private_id,sender_id,grp,
                     up,hidden
		     from messages
		     where id=$id and hidden<$hide");
return mysql_num_rows($result)>0 ? newMessage(mysql_fetch_assoc($result))
                                 : newGrpMessage($grp);
}
?>
