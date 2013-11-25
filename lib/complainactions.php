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

function __construct($row)
{
$this->id=0;
parent::__construct($row);
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
$vars=array('name'      => $action->name,
            'text'      => $action->text,
	    'script_id' => $action->script_id);
if(!$action->id)
  {
  $result=sql(sqlInsert('complain_actions',
			$vars),
	      __FUNCTION__,'insert');
  $action->id=sql_insert_id();
  journal(sqlInsert('complain_actions',
		    jencodeVars($vars,$jencoded)),
	  'complain_actions',$action->id);
  }
else
  {
  $result=sql(sqlUpdate('complain_actions',
			$vars,
			array('id' => $action->id)),
	      __FUNCTION__,'update');
  journal(sqlUpdate('complain_actions',
		    jencodeVars($vars,$jencoded),
		    array('id' => journalVar('complain_actions',$action->id))));
  }
return $result;
}

class ComplainActionListIterator
        extends SelectIterator {

    public function __construct() {
        parent::__construct('ComplainAction',
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

function deleteComplainAction($id)
{
sql("delete from complain_actions
     where id=$id",
    __FUNCTION__);
journal('delete from complain_actions
         where id='.journalVar('complain_actions',$id));
}
?>
