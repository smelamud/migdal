<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/tmptexts.php');
require_once('lib/selectiterator.php');

class ComplainAction
      extends DataObject
{
var $id;
var $type_id;
var $name;
var $text;
var $automatic;
var $script_id;

function ComplainAction($row)
{
$this->id=0;
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
$list=$vars['edittag']==2 ? $this->getEditCorrespondentVars()
                          : $this->getCorrespondentVars();
foreach($list as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if(isset($vars['nameid']))
  $this->text=tmpTextRestore($vars['nameid']);
if(isset($vars['textid']))
  $this->text=tmpTextRestore($vars['textid']);
}

function getCorrespondentVars()
{
return array('text');
}

function getEditCorrespondentVars()
{
return array('type_id','name','text','automatic','script_id');
}

function getWorldVars()
{
return array('type_id','name','text','automatic','script_id');
}

function store()
{
$normal=$this->getNormal();
$result=mysql_query($this->id 
                    ? makeUpdate('complain_actions',
		                 $normal,
				 array('id' => $this->id))
                    : makeInsert('complain_actions',
		                 $normal));
if(!$this->id)
  $this->id=mysql_insert_id();
return $result;
}

function getId()
{
return $this->id;
}

function getTypeId()
{
return $this->type_id;
}

function getName()
{
return $this->name;
}

function getText()
{
return $this->text;
}

function isAutomatic()
{
return $this->automatic;
}

function getScriptId()
{
return $this->script_id;
}

}

class ComplainActionListIterator
      extends SelectIterator
{

function ComplainActionListIterator($typeid)
{
$this->SelectIterator('ComplainAction',
                      "select id,name,automatic
		       from complain_actions
		       where type_id=$typeid");
}

}

function getComplainActionById($id)
{
$result=mysql_query("select id,name,text,automatic,script_id
                     from complain_actions
		     where id=$id")
	     or die('Ошибка SQL при выборке ответа на жалобу');
return new ComplainAction(mysql_num_rows($result)>0
                          ? mysql_fetch_assoc($result)
			  : array());
}

function complainActionExists($id)
{
$result=mysql_query("select id
                     from complain_actions
		     where id=$id")
	     or die('Ошибка SQL при проверке наличия ответа на жалобу');
return mysql_num_rows($result)>0;
}
?>
