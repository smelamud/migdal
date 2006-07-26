<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/selectiterator.php');
require_once('lib/sql.php');

class ComplainAction
      extends DataObject
{
var $id;
var $name;
var $text;
var $script_id;

function ComplainAction($row)
{
$this->id=0;
parent::DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->name=$vars['name'];
$this->text=$vars['text'];
$this->script_id=$vars['script_id'];
}

function getId()
{
return $this->id;
}

function getName()
{
return $this->name;
}

function getText()
{
return $this->text;
}

function getScriptId()
{
return $this->script_id;
}

}

function storeComplainAction(&$action)
{
$jencoded=array('name' => '','text' => '');
$vars=array('name'      => $this->name,
            'text'      => $this->text,
	    'script_id' => $this->script_id);
if(!$this->id)
  {
  $result=sql(makeInsert('complain_actions',
			 $vars),
	      __FUNCTION__,'insert');
  $this->id=sql_insert_id();
  journal(makeInsert('complain_actions',
		     jencodeVars($vars,$jencoded)),
		     'complain_actions',$this->id);
  }
else
  {
  $result=sql(makeUpdate('complain_actions',
			 $vars,
			 array('id' => $this->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('complain_actions',
		     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('complain_actions',$this->id))));
  }
return $result;
}

class ComplainActionListIterator
      extends SelectIterator
{

function ComplainActionListIterator()
{
$this->SelectIterator('ComplainAction',
                      'select id,name
		       from complain_actions');
}

}

function getComplainActionById($id)
{
$result=sql("select id,name,text,script_id
	     from complain_actions
	     where id=$id",
	    __FUNCTION__);
return new ComplainAction(mysql_num_rows($result)>0
                          ? mysql_fetch_assoc($result)
			  : array());
}

function complainActionExists($id)
{
$result=sql("select id
	     from complain_actions
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}
?>
