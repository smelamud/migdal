<?php
# @(#) $Id$

require_once('lib/dataobject.php');

class ComplainType
      extends DataObject
{
var $id;
var $assign;
var $deadline;
var $display;

function ComplainType($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getAssign()
{
return $this->assign;
}

function getDeadline()
{
return $this->deadline;
}

function getDisplay()
{
return $this->display;
}

}

function getComplainTypeByIdent($ident)
{
$result=mysql_query("select id,display
                     from complain_types
		     where ident='$ident'")
	     or die('Ошибка SQL при выборке типа жалобы');
return new ComplainType(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                  : array());
}

function complainTypeExists($id)
{
$result=mysql_query("select id
                     from complain_types
		     where id=$id")
	     or die('Ошибка SQL при выборке типа жалобы');
return mysql_num_rows($result)>0;
}
?>
