<?php
require_once('lib/usertag.php');

define('ENT_NULL',0);
define('ENT_POSTING',1);
define('ENT_FORUM',2);
define('ENT_TOPIC',3);
define('ENT_IMAGE',4);
define('ENT_COMPLAIN',5);
define('ENT_VERSION',6);

class Entry
      extends UserTag
{
var $id;
var $ident;
var $entry;
var $up;
var $track;
var $grp;
var $user_id;
var $group_id;
var $group_login;
var $perms;
var $subject;
var $subject_sort;
var $comment0;
var $comment0_xml;
var $comment1;
var $comment1_xml;
var $body;
var $body_xml;
var $body_format;
var $index0;
var $index1;
var $index2;
var $modbits;
var $answers;
var $last_answer;
var $last_answer_id;

function Entry($row)
{
parent::UserTag($row);
}

function getId()
{
return $this->id;
}

function getIdent()
{
return $this->ident;
}

function getEntry()
{
return $this->entry;
}

function getUpValue()
{
return $this->up;
}

function getTrack()
{
return $this->track;
}

function getGrp()
{
return $this->grp;
}

function getUserId()
{
return $this->user_id;
}

function setUserId($id)
{
$this->user_id=$id;
}

function getGroupId()
{
return $this->group_id;
}

function setGroupId($id)
{
$this->group_id=$id;
}

function getGroupLogin()
{
return $this->group_login;
}

function getGroupName()
{
return $this->getGroupLogin();
}

function getPerms()
{
return $this->perms;
}

function isPermitted($right)
{
return true;
}

function isReadable()
{
return $this->isPermitted(PERM_READ);
}

function isWritable()
{
return $this->isPermitted(PERM_WRITE);
}

function isAppendable()
{
return $this->isPermitted(PERM_APPEND);
}

function isPostable()
{
return $this->isPermitted(PERM_POST);
}

function getSubject()
{
return $this->subject;
}

function getComment0()
{
return $this->comment0;
}

function getComment0XML()
{
return $this->comment0_xml;
}

function getComment0HTML()
{
return mtextToHTML($this->getComment0XML(),MTEXT_LINE);
}

function getComment1()
{
return $this->comment1;
}

function getComment1XML()
{
return $this->comment1_xml;
}

function getComment1HTML()
{
return mtextToHTML($this->getComment1XML(),MTEXT_LINE);
}

function getBody()
{
return $this->body;
}

function getBodyXML()
{
return $this->body_xml;
}

function getBodyHTML()
{
return mtextToHTML($this->getBodyXML(),MTEXT_SHORT);
}

function getBodyFormat()
{
return $this->body_format;
}

function getIndex0()
{
return $this->index0;
}

function getIndex1()
{
return $this->index1;
}

function getIndex2()
{
return $this->index2;
}

function getModbits()
{
return $this->modbits;
}

function getAnswers()
{
return $this->answers;
}

function getLastAnswer()
{
return $this->last_answer;
}

function getLastAnswerId()
{
return $this->last_answer_id;
}

}
?>
